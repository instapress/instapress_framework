<?php
class Instapress_Mvc_Ipml
{
	protected $_Dom = null;
	protected $_Type = null;
	protected $_Task = null;
	protected $_Model = null;
	protected $_html_buffer = null;
	protected $_isComplete = false;

	/**
	 *	Constructor Accepts 3 Arguments
	 *	@var Model Name $type
	 *	@var Model Task $task
	 *	@var Instapress_Core_Html_Dom $dom
	 *	@return Instapress_Mvc_Ipml
	 */
	function __construct($type=null, $task="show", $dom)
	{
		$this->_Dom = $dom;
		$this->_Type = $type;
		$this->_Task = $task;

		$this->_Model =  new $type($task);

		//print_r(Instapress_MVC_Page::$_pageVars);
		//exit;
	}

	/**
	 * Function returns the HTML Parsed from IPML
	 * @return html
	 */
	public function html(){
		// Code To All Parsing logic

		$this->parser($this->_Dom);
		return $this->_html_buffer;
	}

	/**
	 * Function which Parses IPML to HTML
	 * @return HTML
	 */
	protected function parser(&$dom)
	{
		// Dom Parsing
		$this->_htm_buffer = "";
		$this->processSet($dom);		
		$this->parseLoop($dom);
		//$this->parseRegexVars($dom);
		//$this->parseIf($dom);
		//$this->parseHtmlTag($dom);		
		$this->_html_buffer = $dom;
	}

	/**
	 * Function processes Set Request
	 * @return html
	 */
	protected function processSet(&$dom)
	{
		// Code To All Parsing logic

		$set =  $dom->find("ipml:set");
		$attrs = $set[0]->getAllAttributes();

		$args = "";
		foreach($attrs as $key=>$value)	{
			$evalue = Base_Helper :: escape($value);
			$args.= "\"$key||$evalue\",";
		}
		$args = rtrim($args, ",");
		//echo '$this->_Model->set('.$args.');';

		eval('$this->_Model->set('.$args.');');
		$set[0]->outertext = $set[0]->innertext;
		
		//var_dump($this->_Model);
	}


	/*
	 * Function Parses $this->_Dom for Loop
	 * @return null
	 */
	public function parseLoop(&$dom)
	{
		// Code to Parse Loop

		$loopsarr = $dom->find("ipml:loop");
		//loopsarr gives all "ipml:loop" tags into element objects

		$loopchild = null;
		//initialising loopchild as null

		foreach($loopsarr as $looptag )
		{
			$loopchild = $looptag->find("ipml:loop");
			//Check if any other ipml:loop exists inside our first loop

			if(count($loopchild) > 0)
				$this->parseLoop($looptag);
			// Keep on calling same function (parseLoop) until I am sure am in innermost ipml:loop
			
			$increment = $looptag->increment ? $looptag->increment : 1;
			if (is_numeric($looptag->start) and is_numeric($looptag->stop) and is_numeric($increment))
			{
				$start = $looptag->start?$looptag->start : 0;
				$end = $looptag->stop?$looptag->stop : 1;
				//make sure start, end and increment are numeric and set to basic defualt values if no values given

				if (($start<0) OR ($end<1) OR ($increment<1))
				{
					throw new Exception("START and STOP shoudl be greater than 0");
				}
				//start and end shoudl not be a negative number

				if ($end > $this->_Model->getResultcount()-1)
				{
					$end=$this->_Model->getResultcount()-1;
				}
				// make sure if someone call 'end'(stop) greater than real result set, its adjusted to max permissable value

				
				$this->parseIf( $looptag, $start );	
				//$this->parseHtmlTag($looptag);
				$this->parseRegexVars($looptag, $start );
				$this->parseContent( $looptag, $start );				
				
				// since you are inside the innermost loop - process the data for that specific loop

				$looptag->outertext = $looptag->innertext;

				for($i = $start + 1; $i <= $end; $i+=$increment)
				{
					$this->parseIf( $looptag, $i );
					//$this->parseHtmlTag( $looptag);
					$this->parseRegexVars($looptag, $i );
					$this->parseContent( $looptag, $i );
					$looptag->outertext .= $looptag->innertext;
				} // end of For loop

			} //end of numeric If check
			else
			{
				throw new Exception("start, stop and increment should be numeric");
			} //end of else



		} //end of Foreach array Loop

	}

	/*
	 * Function Parses Content based on index $counter
	 * @return null
	 */
	private function parseContent(&$dom, $counter)
	{
		$content = $dom->find("ipml:content");
		$content_type = null;

		foreach( $content as $var )
		{
			$content_type = $var->type;
			$var->outertext = $this->_Model->get($content_type, $counter);
		}
	}

	/*
	 * Function Parses $this->_Dom for HtmlTag
	 * @return null
	 */
	public function parseHtmlTag(&$dom )
	{
		// Code to Parse HtmlTag
		$buffer = null;
		$child = null;
		$htmtags = $dom->find("ipml:htmltag");
		
		foreach( $htmtags as $htmtag )
		{
			$child = $htmtag->find("ipml:htmltag");
			
			if( count($child) > 0 ) {
				$this->parseHtmlTag( $htmtag );
			}

			$stratr = null;
			$attrs = $htmtag->getAllAttributes();

			foreach($attrs as $attrname=>$attrval)
			{
				if( "type" == strtolower($attrname) ) continue;				
					$stratr.=$attrname."=\"".$attrval."\" ";
			}
			
			$val = $htmtag->innertext;
			$tag = "<".$htmtag->type." ".$stratr.">".$val."</".$htmtag->type.">";
			$htmtag->outertext = $tag;

			//$child = $htmtag->find("ipml:htmltag");
		}
	}

	/*
	 * Function Parses $this->_Dom for if
	 * @return null
	 */
	public function parseIf(&$dom,$counter=0)
	{
		// Code to Parse If
		$buffer = null;
		$child = null;
		$iftags = $dom->find("ipml:if");
		
		foreach( $iftags as $iftag )
		{
			$child = $iftag->find("ipml:if");
			if( count($child) > 0 ) {
				$this->parseIf( $iftag );
			}

			$stratr = null;
			$condition = true;
			$attrs = $iftag->getAllAttributes();
			if(isset($attrs['value']))
			{
				$tmpcondition = explode(".",trim($attrs['value']));
				
				if(count($tmpcondition) == 3)
				{
					$$tmpcondition[1] = isset(Instapress_Mvc_Page::$_pageVars[strtoupper($tmpcondition[0])]["$tmpcondition[1]"]) ? Instapress_MVC_Page::$_pageVars[strtoupper($tmpcondition[0])]["$tmpcondition[1]"] : null;
					$condition = ( $$tmpcondition[1] == "$tmpcondition[2]" ) ? true : false;
				}
				elseif( count($tmpcondition) == 2)
				{
					$$tmpcondition[1] = $this->_Model->ipmlIf($tmpcondition[0], $counter);
					$condition = ( $$tmpcondition[1] == "$tmpcondition[1]" ) ? true : false;
				}
				else
				{
					throw new Exception("IPML IF TAG SYNTAX ERROR");
				}
			}

			if( !$condition )
				$iftag->outertext = '';
			else
				$iftag->outertext = $iftag->innertext;
		}
	}

	/**
	 * Function subsitutes regex vars
	 * @param $dom, DOM element
	 * @return null
	 */
	public function parseRegexVars( &$dom, $counter=0)
	{
		preg_match_all("({([A-Za-z0-9\.]+)})", $dom->outertext, $subVars);
		$tempArs = $subVars;
		
		//print_r($tempArs);
		//print_r($subVars);

		$rpls = $dom->outertext;
		//print_r($rpls);
				
		for( $c = 0; $c < count( $subVars[1] ); $c++)
		{
			$subVars[0][$c] = trim($subVars[1][$c]);
			$varVal = explode(".",$subVars[1][$c]);
			if(2 == count($varVal))
			{
				$subVars[1][$c] = isset(Instapress_Mvc_Page::$_pageVars[$varVal[0]][$varVal[1]]) ? Instapress_Mvc_Page::$_pageVars[$varVal[0]][$varVal[1]] : 'Variable not found' ;
			}
			elseif(1 == count($varVal))
			{
				$subVars[1][$c] = $this->_Model->get($varVal[0], $counter);
			}
			else
			{
				throw new Exception("IPML VARIABLE TAG SYNTAX ERROR");
			}
			
			$rpls = str_replace($tempArs[0][$c], $subVars[1][$c], $rpls);
			
		}
 $dom->outertext.=$rpls;
 //echo  $dom->outertext;echo "\n\n\n\n\n\n\n\n------------------------\n\n\n\n\n\n";
		//print_r($tempArs);
		//print_r($subVars);

		//$tempDom = new Instapress_Core_Html_Dom;
	    //$tempDom->load($rpls, true);
	    //$dom = $tempDom;
	}
}
?>