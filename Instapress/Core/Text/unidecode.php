<?php
/**
 *=--------------------------------------------------------------------------=
 * unidecode.php
 *=--------------------------------------------------------------------------=
 * This file contains a couple of functions that will take a string with
 * web-browser encoded unicode characters and decode the string returning 
 * the full UTF-8 string for it.
 *
 * String formats supported are:  
 *
 * &#[0-9]+
 * &#[Xx][0-9a-fA-F]+
 * \[Xx][0-9a-fA-F]+ 
 * \[uU][0-9a-fA-F]+
 * \[0-9a-fA-F]{1,4}
 *
 * If you know of any other format strings that are supported by various
 * browsers, please contact me at http://chipmunkninja.com
 * Author: marc, 2006-07-07
 */

/**
 * Copyright (c) 2006
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

/**
 *=--------------------------------------------------------------------------=
 * unidecodeString
 *=--------------------------------------------------------------------------=
 * This function takes an input string and looks for any unicode escape
 * sequences used in browsers.  Those sequences we currently know about and
 * decode are listed at the top of this file (and totally manage to screw up
 * Emacs' PHP code-colouring mode for some reason -- it even crashed it 
 * earlier today :-)).
 *
 * The returned value is the decoded string.
 *
 * Parameters:
 *    $in_string              - string to attempt to decode.
 *
 * Returns:
 *    A string with any unicode escape sequences decoded.
 *
 * Notes:
 *    The returned string is always UTF-8.
 */
function unidecodeString($in_string)
{
    $pos = 0;
    $max = mb_strlen($in_string);

    $outputString = array();

    while ($pos < $max and ($ch = mb_substr($in_string, $pos, 1)) !== FALSE)
    {
        switch ($ch)
        {
            case '&':
                if (($pushme = decodeAmpChar($in_string, $pos)) !== FALSE)
                {
                    $outputString[] = $pushme['char'];
                    $pos += $pushme['cchars'];
                }
                else
                {
                    $outputString[] = $ch;
                    $pos++;
                }
                break;
            case '\\':
                if ($pushme = decodeSlashChar($in_string, $pos) !== FALSE)
                {
                    $outputString[] = $pushme['char'];
                    $pos += $pushme['cchars'];
                }
                else
                {
                    $outputString[] = $ch;
                    $pos++;
                }
                break;
            default:
                $outputString[] = $ch;
                $pos++;
        }
    }

    return implode('', $outputString);
}


/**
 *=--------------------------------------------------------------------------=
 * decodeAmpChar
 *=--------------------------------------------------------------------------=
 * If we get an ampersand in an input string, this function looks to see if
 * it's one of the escape sequences that starts with such a char.
 *
 * Parameters:
 *    $in_string              - The string with which we are working.
 *    $in_pos                 - The index in the string parameter of the &
 *                              char that caused us to call this fn.
 *
 * Returns:
 *    An array with two values:  The character we found (as a UTF-8 string)
 *    and the number of characters we snarfed from the input string to build
 *    this character.
 *
 * Notes:
 *    This function and decodeSlashChar should share more code.
 */
function decodeAmpChar($in_string, $in_pos)
{
    $haveSeenDigit = FALSE;
    $cchars = $in_pos;
    $base = 10;
    $unicodeOrd = 0;
    $max = mb_strlen($in_string);

    if (mb_substr($in_string, $in_pos, 1) !== '&')
        return FALSE;

    $cchars++;

    if (mb_substr($in_string, $cchars, 1) !== '#')
        return FALSE;

    $cchars++;

    if (mb_substr($in_string, $cchars, 1) == 'x')
    {
        $base = 16;
        $cchars++;
    }

    while ($cchars < $max and ($ch = mb_substr($in_string, $cchars, 1)) !== FALSE)
    {
        switch ($ch)
        {
            case ';':
                $cchars++;
                if ($haveSeenDigit)
                    return array('char' => unichr($unicodeOrd), 'cchars' => $cchars - $in_pos);
                else
                    return FALSE;
                break;

            case 'a': case 'A': case 'b': case 'B': case 'c': case 'C': 
            case 'd': case 'D': case 'e': case 'E': case 'f': case 'F': 
                if ($base != 16)
                    return FALSE;
                else
                {
                    $unicodeOrd *= $base;
                    $chl = strtolower($ch);
                    $ord = 10 + (ord($chl) - ord('a'));
                    $base += $ord;
                }
                $haveSeenDigit = TRUE;
                break;

            case '0':case '1':case '2':case '3':case '4':case '5':case '':case '6':
            case '7':case '8':case '9':case '0':
                $unicodeOrd *= $base;
                $unicodeOrd += (ord($ch) - ord('0'));
                $haveSeenDigit = TRUE;
                break;
            default:
                return FALSE;
        }

        $cchars++;
    }

    if ($haveSeenDigit)
        return array('char' => unichr($unicodeOrd), 'cchars' => $cchars - $in_pos);
    else
        return FALSE;
}


/**
 *=--------------------------------------------------------------------------=
 * decodeSlashChar
 *=--------------------------------------------------------------------------=
 * If we get an backslash in an input string, this function looks to see if
 * it's one of the escape sequences that starts with such a char.
 *
 * Parameters:
 *    $in_string              - The string with which we are working.
 *    $in_pos                 - The index in the string parameter of the \
 *                              char that caused us to call this fn.
 *
 * Returns:
 *    An array with two values:  The character we found (as a UTF-8 string)
 *    and the number of characters we snarfed from the input string to build
 *    this character.
 *
 * Notes:
 *    This function and decodeAmpChar should share more code.
 */
function decodeSlashChar($in_string, $in_pos)
{
    $haveSeenDigit = FALSE;
    $cchars = $in_pos;
    $base = 16;
    $unicodeOrd = 0;
    $max = mb_strlen($in_string);

    if (mb_substr($in_string, $in_pos, 1) !== '\\')
        return FALSE;

    $cchars++;

    $nc = strtolower(mb_substr($in_string, $cchars, 1));
    switch ($nc)
    {
        case 'x':case 'u':
            $cchars++;
            break;

        case 'a':case 'b':case 'c':case 'd':case 'e':case 'f':
        case '0':case '1':case '2':case '3':case '4':
        case '5':case '6':case '7':case '8':case '9':
            break;

        default:
            return FALSE;
    }


    while ($cchars < $max and ($ch = mb_substr($in_string, $cchars, 1)) !== FALSE)
    {
        switch ($ch)
        {
            case ';':
                $cchars++;
                if ($haveSeenDigit)
                    return array('char' => unichr($unicodeOrd), 'cchars' => $cchars - $in_pos);
                else
                    return FALSE;
                break;

            case 'a': case 'A': case 'b': case 'B': case 'c': case 'C': 
            case 'd': case 'D': case 'e': case 'E': case 'f': case 'F': 
                $unicodeOrd *= $base;
                $chl = strtolower($ch);
                $ord = 10 + (ord($chl) - ord('a'));
                $base += $ord;
                $haveSeenDigit = TRUE;
                break;

            case '0':case '1':case '2':case '3':case '4':case '5':case '':case '6':
            case '7':case '8':case '9':case '0':
                $unicodeOrd *= $base;
                $unicodeOrd += (ord($ch) - ord('0'));
                $haveSeenDigit = TRUE;
                break;
            default:
                return FALSE;
        }

        $cchars++;
    }

    if ($haveSeenDigit)
        return array('char' => unichr($unicodeOrd), 'cchars' => $cchars - $in_pos);
    else
        return FALSE;
}


/**
 *=--------------------------------------------------------------------------=
 * unichr
 *=--------------------------------------------------------------------------=
 * This function takes a numeric value expressing a Unicode character and
 * returns it as a UTF-8 string.  This string can be anywhere from 1 to 4
 * bytes long.
 *
 * Parameters:
 *    $num                    - The Unicode value to encode as a string.
 *
 * Returns:
 *    A string representing the given Unicode value.  This string can be
 *    anywhere from 1 to 4 bytes long.
 */
function unichr($num)
{
    if($num < 128)
        return chr($num);
    if($num < 2048)
        return chr(($num >> 6) + 192) . chr(($num&63) + 128);
    if($num < 65536)
        return chr(($num >> 12) + 224).chr((($num >> 6)&63) + 128).chr(($num&63) + 128);
    if($num<2097152)
        return chr(($num >> 18) + 240).chr((($num >> 12)&63) + 128).chr((($num >> 6)&63) + 128) .chr(($num&63) + 128);
    return '';
}
?>