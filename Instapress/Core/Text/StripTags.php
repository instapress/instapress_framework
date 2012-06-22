<?php
/**
 *=--------------------------------------------------------------------------=
 * strip_tags.inc
 *=--------------------------------------------------------------------------=
 * This contains code to do some tag stripping that is both MBCS-safe and
 * more powerful than the default tag stripping available on the PHP 
 * strip_tags function.  This basically involves writing a little HTML
 * parsing state machine.  I'm not the best at this, but it seems to work
 * quite well, and isn't terribly inefficient.
 *
 * Author: marc, began 2005-05-08
 *
 * Note that this class assumes all input is UTF-8.
 *
 * UNDONE: marc, 2005-05-08: add support for other character set encodings.
 *
 * UPDATED: marc, 2005-09-16: completely rewrote string processing code to
 *                be better at memory management (it's a bit slower, but
 *                will harras the garbage collector less).
 * UPDATED: marc, 2006-07-07: added code to finally fix the problem of   
 *                unicode encoded characters in attributes.
 */

/**
 * Copyright (c) 2005-2006
 *      Marc Wandschneider. All Rights Reserved.
 *
 * Redistribution and use in source and binary forms, with or without
 * modification, are permitted provided that the following conditions
 * are met:
 * 1. Redistributions of source code must retain the above copyright
 *    notice, this list of conditions and the following disclaimer.
 * 2. The name Marc Wandschneider may not be used to endorse or promote
 *    products derived from this software without specific prior
 *    written permission.
 *
 * THIS SOFTWARE IS PROVIDED BY THE AUTHOR ``AS IS'' AND ANY EXPRESS OR
 * IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE IMPLIED WARRANTIES
 * OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE ARE DISCLAIMED.
 * IN NO EVENT SHALL THE AUTHOR BE LIABLE FOR ANY DIRECT, INDIRECT,
 * INCIDENTAL, SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING,
 * BUT NOT LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS
 * OF USE, DATA, OR PROFITS; DEATH OF YOUR PET CAT, DOG, OR FISH; OR
 * BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY,
 * WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE
 * OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN
 * IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 */
require_once('unidecode.php');

/**
 *=--------------------------------------------------------------------------=
 * StripTags
 *=--------------------------------------------------------------------------=
 * This class is how you use the tag stripping functionality.  In short, you
 * create an instance of it, passing to it an array keyed by HTML elements
 * that you would like to permit (or deny, based on the value of the second
 * parameter).  The VALUES of these indices are themselves arrays of permitted
 * attributes for each tag.  Thus, if we wanted to allow 'strong', 'em', 'a'
 * with href and title, and 'img' with src, border, and alt, we would 
 * create the object as follows:
 *
 * $tagsAndAttrs = array(
 *     'strong' => array(),    // no attrs allowed.
 *     'em' => array(),
 *     'a' => array('title', 'href'),
 *     'img' => array('src', 'border', 'alt')
 * );
 *
 * $st = new StripTags($tagsAndAttrs);
 *
 * Usage is then as simple as:
 *
 * $malicious = <<<EOS
 *   This is an eeveeeillll
 *   <script> document.location = "http://evilsite.url"; </script>
 *   string.
 * EOS;
 *
 * $fixed = $st->strip($malicious);
 */
class StripTags
{
  /**
   * FilterAttributes Property:
   *
   * This property controls whether or not we should filter attribute
   * strings on markup elements.  If turned on, we will decode unicode
   * escape sequences in attribute strings, and if the string contains
   * any of the "illegal" values in $s_filterPrefixes, we will put
   * a string to the effect of "XSS PROTECT" at the beginning to prevent
   * the browser from processing it..
   */
  public $FilterAttributes = TRUE;

  /**
   * List of tags and attributes.
   */
  protected $m_tagsAndAttrs;

  /**
   * Whether they are inclusive or exclusive lists 
   */
  protected $m_tagsInclusive;

  /**
   * These are used to maintain the state machine we use to parse through
   * strings.
   */
  protected $m_fullString;      // the full string we got from the user to strip.
  protected $m_max;             // max size of m_fullString.
  protected $m_x;               // current position in the string.
  protected $m_output;          // the output string we're building.

  protected $m_currentTag;      // as we process attrs, the tag we're in.


  /**
   * Possible ways to end a tag -- SLASHGT /> and GT, >.
   */
  const SLASHGT = 0;
  const GT = 1;


  /**
   * This is a list of bad strings we'll look for in HTML attribute values
   * and filter out before returning them, unless 'FilterAttributes' is set
   * to false.
   */
  protected static $s_filterPrefixes = array(
    'javascript:',
    'vbscript:',
    'livescript:',
    'mocha:',
    'script:',
    'perl:',               // few browsers support these last two, but ...
    'python:'
  );



  /**
   *=------------------------------------------------------------------------=
   * __construct
   *=------------------------------------------------------------------------=
   * Initialises a new instance of this class.  We require a list of tags and
   * attributes and a flag saying whether they are an inclusive or exclusive
   * set.
   *
   * Parameters:
   *    $in_tagsAndAttrs    - array keyed by tags, with values being arrays
   *                          of allowed attributes on those keys.
   *    $in_tagsincl        - [optional] are tags inclusive (only listed 
   *                          tags are allowed) or exclusive (all but those
   *                          tags are permitted).
   */
  public function __construct
  (
    $in_tagsAndAttrs,
    $in_tagsincl = TRUE
  )
  {
    if (!is_null($in_tagsAndAttrs) and !is_array($in_tagsAndAttrs))
      throw new InvalidArgumentException('$in_tagsAndAttrs');

    /**
     * save out the local vars, making sure that they have at least 
     * some value set.
     */
    $this->m_tagsAndAttrs = $in_tagsAndAttrs;
    if ($this->m_tagsAndAttrs === NULL)
      $this->m_tagsAndAttrs = array();
    $this->m_tagsInclusive = $in_tagsincl;
  }


  /**
   *=------------------------------------------------------------------------=
   * strip
   *=------------------------------------------------------------------------=
   * Removes evil baddie tags from the input string, excepting (or restricting
   * to) those tags specimified in the arguments to the constructor.
   *
   * Parameters:
   *      $in_string           - strip me please.
   * 
   *
   * Returns:
   *      stripped string.
   *
   * Notes:
   *      STRING IS ASSUMED TO BE UTF-8.
   */
  public function strip($in_string)
  {
    if ($in_string === NULL or $in_string == '')
      return '';

    /**
     * 1. explode the string into its constituent CHARACTERS (not bytes,
     *    which in UTF-8 are most certainly not the same thing).
     */
    $this->m_fullString = $in_string;
    $this->m_max = mb_strlen($this->m_fullString);
    $this->m_x = 0;

    /**
     * 2. Parse the string.  We will be quite robust about this, supporting
     *    arbitrary whitespace characters and > and < chars within attribute
     *    values (which is valid HTML, but prolly not valid XHTML).
     *    This will require setting up a bit of a state machine, which is a
     *    pain, but worth it.  Robustness is good.
     */
    $this->m_output = array();
    $ch = $this->peekCurrentChar();
    while (!$this->atEnd())
    {
      if ($ch != '<')
          $this->m_output[] = $ch;
      else
        $this->processTag();
      $ch = $this->getNextChar();
    }

    return $this->rebuildString($this->m_output);
  }


  /**
   *=------------------------------------------------------------------------=
   * processTag
   *=------------------------------------------------------------------------=
   * We have encountered a tag in our string.  See if it's a valid tag and
   * process (out) any attributes within it.
   */
  protected function processTag()
  {
    /**
     * 1. Get the name of the tag and see if it's valid or not.
     */
    $tagName = $this->getTagName();
    if ($tagName === NULL)
      return ;                // there's nothing there!  

    if (!$this->isPermissibleTag($tagName))
    {
      $this->processEndOfTag();
      return;
    }
    else if (substr($tagName, 0, 1) == '/')
    {
      /**
       * If it's a closing tag, just consume everything up until the
       * closing tag character.
       */
      $this->processEndOfTag(FALSE);
      $fullTag = "<$tagName>";

      $l = mb_strlen($fullTag);
      for ($z = 0; $z < $l; $z++)
          $this->m_output[] = mb_substr($fullTag, $z, 1);
    }
    else
    {
      /**
       * tag's valid.  go and get any attributes associated with it.
       */
      $this->m_currentTag = $tagName;
      $attrs = $this->processAttributes();
      $fullTag = "<$tagName";
      foreach ($attrs as $attr)
      {
        if ($attr['value'] != '')
        {
          $qc = $attr['quote'];
          $fullTag .= " {$attr['name']}=" . $qc
            . $this->furtherProcess($attr['value']) . $qc;
        }
        else
          $fullTag .= " {$attr['name']}";
      }

      /**
       * figure out closing tag type and duplicate.
       */
      $tagType = $this->processEndOfTag();
      $fullTag .= ($tagType == StripTags::SLASHGT) ? '/>' : '>';

      $l = mb_strlen($fullTag);
      for ($z = 0; $z < $l; $z++)
          $this->m_output[] = mb_substr($fullTag, $z, 1);
    }
  }


  /**
   *=------------------------------------------------------------------------=
   * getTagName
   *=------------------------------------------------------------------------=
   * Given that we are positioned RIGHT after the opening < char, go and
   * find the name of the tag.  We will actually handle the case where we 
   * are given an empty tag, like < > or < />.
   *
   * Returns:
   *      string name or NULL indicating empty tag (or EOS)
   */
  protected function getTagName()
  {
    /**
     * skip over any space chars.
     */
    $this->moveNextChar();
    $this->consumeWhiteSpace();
    $tag = array();

    /**
     * Is it a closing tag??
     */
    if ($this->hasMoreCharacters() and $this->peekCurrentChar() == '/')
    {
      $tag[] = '/';
      $this->moveNextChar();
    }

    /**
     * now get anything until the next whitespace character or /> or >.
     */
    $ch = $this->peekCurrentChar();
    while (!$this->atEnd()
           and !$this->isSpaceChar($ch)
           and ($ch != '>')
           and !($ch == '/' and $this->peekNextChar() == '>'))
    {
      $tag[] = $ch;
      $ch = $this->getNextChar();
    }

    if (count($tag) == 0)
      return NULL;
    else
      return $this->rebuildString($tag);
  }


  /**
   *=------------------------------------------------------------------------=
   * isPermissibleTag
   *=------------------------------------------------------------------------=
   * Checks to see whether the given tag is valid or not given the user's
   * options to our constructor.
   *
   * Parameters:
   *      $in_tagName                   - tag name to check.
   *
   * Returns:
   *      TRUE == ok, FALSE == AAAIEEEE!!!
   */
  protected function isPermissibleTag($in_tagName)
  {
    /**
     * If it's a closing tag, remove the / for the purposes of this search.
     */
    if (substr($in_tagName, 0, 1) == '/')
      $check = substr($in_tagName, 1);
    else
      $check = $in_tagName;

    /**
     * Zip through all the tags in the array seeing if it is
     * valid.  We have to see if they gave us an inclusive or
     * exclusive list of permissible tags.
     */
    foreach ($this->m_tagsAndAttrs as $tag => $attrs)
    {
      $t = trim($tag);
      if ($this->m_tagsInclusive)
      {
        if ($t == $check)
          return TRUE;
      }
      else
      {
        if ($t == $check)
          return FALSE;
      }
    }

    return $this->m_tagsInclusive ? FALSE : TRUE;
  }



  /**
   *=------------------------------------------------------------------------=
   * processEndOfTag
   *=------------------------------------------------------------------------=
   * Skip all characters looking for the end of tag (> or />).  Unfortunately,
   * we cannnot simply zip through the string looking for these two 
   * character sequences, as they might be embedded within quotes.  We thus
   * have to manage a little state and remember whether or not we are in
   * quotes ...
   *
   * Parameters:
   *      $in_slashGTOk          - is /> allowed or only >  ??
   *
   * Returns:
   *      SLASHGT or GT, indicating which type of closing tag was found.
   */
  protected function processEndOfTag($in_slashGTOk = TRUE)
  {
    /**
     * This is not as simple as just looking for the next > character,
     * as that might be within an attribute string.  We will thus
     * have to maintain some state and make sure that we handle that
     * case properly.
     */
    $in_quote = FALSE;
    $quote_char = '';

    while (!$this->atEnd())
    {
      $ch = $this->peekCurrentChar();
      switch ($ch)
      {
        case '\'':
          if ($in_quote and $quote_char == '\'')
            $in_quote = FALSE;
          else if (!$in_quote)
          {
            $in_quote = TRUE;
            $quote_char = '\'';
          }
          $this->moveNextChar();
          break;

        case '"':
          if ($in_quote and $quote_char == '"')
            $in_quote = FALSE;
          else if (!$in_quote)
          {
            $in_quote = TRUE;
            $quote_char = '"';
          }

          $this->moveNextChar();
          break;

        case '/':
          if (!$in_quote
              and ($this->hasMoreCharacters())
              and ($this->peekNextChar() == '>')
              and $in_slashGTOk)
          {
            $this->moveNextChar();
            return StripTags::SLASHGT;
          }
          
          $this->moveNextChar();
          break;

        case '>':
          if (!$in_quote)
          {
            return StripTags::GT;
          }

          $this->moveNextChar();
          break;

        default:
          $this->moveNextChar();
          break;
      }
    }
  }


  /**
   *=------------------------------------------------------------------------=
   * processAttributes
   *=------------------------------------------------------------------------=
   * Given that we have a valid tag name, we are now going to go process its
   * attributes and see how we like them.  We will assume that all are in one
   * of the two following formats:
   *
   *  attribute = value   [valid is quoted string or single word]
   *  attribute           [attribute is sequence of non-space chars]
   *
   * Returns:
   *      an array of 'attrName' => 'attrValue' pairs.
   *
   * Note:
   *      the $m_x 'cursor' is pointing to the first space char right after
   *      the attr name.
   */
  protected function processAttributes()
  {
    $attrs = array();

    while (($attrDetails = $this->nextAttribute()) !== NULL)
    {
      if ($this->isPermissibleAttribute($attrDetails['name']))
      {
        if (!isset($attrDetails['quote']))
          $attrDetails['quote'] = '\'';
        $attrs[] = $attrDetails;
      }
    }

    return $attrs;
  }


  /**
   *=------------------------------------------------------------------------=
   * nextAttribute
   *=------------------------------------------------------------------------=
   * We are processing a tag.  Get the next attribute, or return NULL if there
   * are no mo'.
   *
   * Returns:
   *      an array with 'attrName' => 'attrValue' or NULL if there is not 
   *      another attribute.
   */
  protected function nextAttribute()
  {
    /**
     * skip over any space chars.
     */
    $this->consumeWhiteSpace();

    /**
     * 1. Attribute Name.
     *
     * Now get anything until the next whitespace character, = character,
     * end of tag (> or />), or end of buffer.
     */
    $attr = array();
    $ch = $this->peekCurrentChar();
    while (!$this->atEnd()
           and !$this->isSpacechar($ch)
           and $ch != '='
           and $ch != '>'
           and !($ch == '/' and $this->peekNextChar() == '>'))
    {
      $attr[] = $ch;
      $ch = $this->getNextChar();
    }

    /**
     * If it's at the end of of the tag or the end of the string, then
     * evidence suggests we only got an attribute name.
     */
    $ch = $this->peekCurrentChar();
    if ($this->atEnd()
        or $ch == '>'
        or $ch == '/')
    {
      if (count($attr) > 0)
        return array('name' => $this->rebuildString($attr), 'value' => '');
      else
        return NULL;
    }

    /**
     * We got a space.  If there is an = sign ahead after only whitespaces,
     * then that will point to the value.  Otherwise, we only have an attr
     * name.
     */
    if ($this->isSpaceChar($this->peekCurrentChar()))
    {
      if (!$this->peekAheadInTag('=')) 
        return array('name' => $this->rebuildString($attr), 'value' => '');
    }

    /**
     * otherwise, if we're here, then we're at an equals sign.
     */
    $this->moveNextChar();
    $this->consumeWhiteSpace();
    if ($this->atEnd())
      return array('name' => $this->rebuildString($attr), 'value' => '');
    
    /**
     * 2. Attribute Value
     *
     * Now get anything until the next whitespace character,
     * end of tag (> or />), or end of buffer.  We have to be careful,
     * however, to be able to handle a string enclosed attribute
     * value, such as 'this is a value'.
     */
    $in_quote = FALSE;
    $quote_char = '';
    $value = array();
    $ch = $this->peekCurrentChar();
    if ($this->isQuoteChar($ch))
    {
      $in_quote = TRUE;
      $quote_char = $ch;
      $this->moveNextChar();
    }

    /**
     * This is an annoying expression.  We want to skip characters IF:
     *
     * - we are in a quoted attr value and the current character is not
     *   the closing quote.
     * OR
     * - we are NOT in a quoted attr value and the current character is
     *   not:
     *    - EOS
     *    - >
     *    - />
     *    - white space
     *
     * In all cases, don't go past EOS.
     */
    $ch = $this->peekCurrentChar();
    while (($in_quote
            and !$this->atEnd()
            and $ch != $quote_char)
           or (!$in_quote
               and  (!$this->atEnd()
                     and !$this->isSpaceChar($ch)
                     and ($ch != '>')
                     and !($ch == '/' 
                           and $this->peekNextChar() == '>'))))
    {
      $value[] = $ch;
      $ch = $this->getNextChar();
    }

    if (!$this->atEnd() and $in_quote )
    {
      $this->moveNextChar();
    }

    /**
     * return the attribute name and value.
     */
    return array('name' => $this->rebuildString($attr), 
                 'value' => $this->rebuildString($value),
                 'quote' => $quote_char);
  }


  /**
   *=------------------------------------------------------------------------=
   * peekAheadInTag
   *=------------------------------------------------------------------------=
   * Looks to see if the NEXT NON-WHITESPACE character is the specified
   * character.
   *
   * Parameters:
   *      $in_char                      - character to look for.
   *
   * Returns:
   *      TRUE -- it is!  FALSE, it's not!
   *
   * Notes:
   *      IFF TRUE is returned, then $this->m_x is updated to point at this
   *      character.
   */
  protected function peekAheadInTag($in_char)
  {
    $x = $this->m_x;
    $ch = $this->peekCurrentChar();
    while ($x < $this->m_max and $this->isSpaceChar($ch)
           and $ch != $in_char)
    {
      $x++;
      $ch = mb_substr($this->m_fullString, $x, 1);
    }

    if ($x == $this->m_max)
      return FALSE;
    else if ($ch == $in_char)
    {
      $this->m_x = $x;
      return TRUE;
    }
    else
      return FALSE;
  }


  /**
   *=------------------------------------------------------------------------=
   * isPermissibleAttribute
   *=------------------------------------------------------------------------=
   * Checks to see whether the given attribute is valid or not given the
   * user's options to our constructor.
   *
   * Parameters:
   *      $in_attrName                   - attribute name to check.
   *
   * Returns:
   *      TRUE == ok, FALSE == AAAIEEEE!!!
   */
  protected function isPermissibleAttribute($in_attrName)
  {
    $attrs = $this->m_tagsAndAttrs[$this->m_currentTag];
    if ($attrs === NULL)
      $attrs = array();

    /**
     * Zip through all the attributes in the array seeing if it is
     * valid.  We have to see if they gave us an inclusive or
     * exclusive list of permissible attributes.
     */
    $check = strtolower($in_attrName);
    foreach ($attrs as $attr)
    {
      $t = strtolower(trim($attr));
      if ($t == $check)
        return TRUE;
    }

    return FALSE;
  }


  /**
   *=------------------------------------------------------------------------=
   * consumeWhiteSpace
   *=------------------------------------------------------------------------=
   * Sucks up characters as long as there are characters left in the string
   * AND they are whitespace characters.
   *
   * Notes:
   *      This function does not accept double-wide space characters such as
   *      those seen in Asian character sets.  We assume these are not valid
   *      in HTML documents.
   */
  protected function consumeWhiteSpace()
  {
    while (!$this->atEnd() and $this->isSpaceChar($this->peekCurrentChar()))
      $this->moveNextChar();
  }


  /**
   *=------------------------------------------------------------------------=
   * isSpaceChar
   *=------------------------------------------------------------------------=
   * Is the next character a whitespace character.  We do NOT include
   * double wide space characters from Asian character sets.
   *
   * Parameters:
   *      $in_char                      - Character to examiminine.
   *
   * Returns:
   *      TRUE == it's a Space!   FALSE == it's not a space!
   */
  protected function isSpaceChar($in_char)
  {
    switch ($in_char)
    {
      case ' ':
      case "\t":
      case "\n":
      case "\r":
      case "\v":
        return TRUE;
      default:
        return FALSE;
    }
  }


  /**
   *=------------------------------------------------------------------------=
   * isQuoteChar
   *=------------------------------------------------------------------------=
   * Asks whether the given UTF-8 character is a quote character.  We only
   * char about ISO-8859 quote characters, namely " and '.
   *
   * Parameters:
   *      $in_char                 - check me please.
   *
   * Returns:
   *      TRUE, si, ees a quote.
   *      FALSE, no mang, ees no a quote.
   */
  protected function isQuoteChar($in_char)
  {
    switch ($in_char)
    {
      case '\'':
      case '"':
        return TRUE;
    }

    return FALSE;
  }


  /**
   *=------------------------------------------------------------------------=
   * rebuildString
   *=------------------------------------------------------------------------=
   * Takes an array of characters and reconstructs a string for it.
   *
   * Parameters:
   *      $in_charArray                  - array containing chars
   *
   * Returns:
   *      string for those .
   */
  protected function rebuildString($in_charArray)
  {
    return implode('', $in_charArray);
  }


  /**
   *=------------------------------------------------------------------------=
   * furtherProcess
   *=------------------------------------------------------------------------=
   * This function actually inspects the text of an attribute string, and 
   * takes further action to try and prevent XSS by removing colons:
   *
   * Parameters:
   *     $in_attrString                 - the attribute string to process more
   *
   * Returns:
   *     processed (and hopefully safe) attribute string.
   *
   * Notes:
   *     this function does nothing if FilterAttributes is set to FALSE
   */
  protected function furtherProcess($in_attrString)
  {
    if (!$this->FilterAttributes)
      return $in_attrString;

    //
    // 1. decode any unicode characters in the string.
    //
    $decoded = unidecodeString($in_attrString);

    //
    // 2, if there are no : chars, then this is easy.
    //
    $p = mb_strpos($decoded, ':');
    if ($p === FALSE)
      return $in_attrString;

    //
    // 3. if there are, then look for bad strings before them.
    //
    $filters = implode('|', self::$s_filterPrefixes);
    if (eregi("^({$filters})", trim($decoded)) !== FALSE)
      return 'XSS PROTECT...' . $in_attrString;

    return $in_attrString;
  }


  /**
   *=------------------------------------------------------------------------=
   * escapeDoubleQuotes
   *=------------------------------------------------------------------------=
   * Double quotes will be replaced by &quot;
   *
   * Parameters:
   *      $in_string               - string to fix.
   *
   * Returns:
   *      fixed string.
   */
  protected function escapeDoubleQuotes($in_string)
  {
    return ereg_replace('"', '&quot;', $in_string);
  }



  /**
   *=------------------------------------------------------------------------=
   * atEnd
   *=------------------------------------------------------------------------=
   * Returns a Boolean indicating whether or not we're at the end of our
   * string.
   *
   * Returns:
   *       TRUE if there are no more chars to process, false otherwise.
   */
  protected function atEnd()
  {
    return ($this->m_x < $this->m_max) ? FALSE : TRUE;
  }


  /**
   *=------------------------------------------------------------------------=
   * hasMoreCharacters
   *=------------------------------------------------------------------------=
   * Returns a Boolean indicating whether or not there are more characters to
   * read.
   *
   * Returns:
   *       TRUE if there are more characters to process, FALSE if not.
   */
  protected function hasMoreCharacters()
  {
    return ($this->m_x < $this->m_max - 1) ? TRUE : FALSE;
  }

  /**
   *=------------------------------------------------------------------------=
   * getNextChar
   *=------------------------------------------------------------------------=
   * Increments the current position counter AND THEN returns the next
   * character in the character stream.
   *
   * Returns:
   *        A character or NULL if there are no more.
   */
  protected function getNextChar()
  {
    return mb_substr($this->m_fullString, ++$this->m_x, 1);
  }


  /**
   *=------------------------------------------------------------------------=
   * peekCurrentChar
   *=------------------------------------------------------------------------=
   * Fetches the current character from the character stream.  You should 
   * cache this whenever possible because it's a wee bit expensive to
   * compute.
   *
   * Returns:
   *      The current character in the stream, or NULL if we're past EOS.
   */
  protected function peekCurrentChar()
  {
    if ($this->m_x >= $this->m_max)
      return NULL;
    else
      return mb_substr($this->m_fullString, $this->m_x, 1);
  }


  /**
   *=------------------------------------------------------------------------=
   * peekNextChar
   *=------------------------------------------------------------------------=
   * Peeks at what is the next character in the character stream.
   *
   * Returns:
   *      The NEXT character in the stream, or NULL if we're past EOS.
   */
  protected function peekNextChar()
  {
    if ($this->m_x < $this->m_max)
      return mb_substr($this->m_fullString, $this->m_x + 1, 1);
    else
      return NULL;
  }


  /**
   *=------------------------------------------------------------------------=
   * moveNextChar
   *=------------------------------------------------------------------------=
   * Moves our 'pointer' to be at the next character in the input string.
   */
  protected function moveNextChar()
  {
      $this->m_x++;
  }
}
?>