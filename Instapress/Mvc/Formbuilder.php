<?php
class Instapress_Mvc_Formbuilder {
	
	private $formelem = null;
	
	function __construct($formelements,$formtype='add') {
		$this->formelem = $formelements[$formtype];	
	}
	
	private function encodeinfieldset($text, $caption='Title') {
	if( 'title' != strtolower(trim($caption)))
		return '<fieldset><legend>'.$caption.'</legend>'.$text.'</fieldset>';
	else
		return $text;	
	}
	
	private function divcapsulate($str) {
		return '<li>'.$str.'</li>';
	}
	
	private function createSmallTextElement( $ename, $evalue, $caption='Title') {
		$element = '<input type="text" ';
		$element .= 'name="'.$ename.'" ';
		$element .= 'value="'.$evalue.'" ';
		$element .= '/>';
		return $this->divcapsulate($this->encodeinfieldset($element,$caption));
	}
	
	private function createEnumElement( $ename, $evals, $caption='Title') {
		$valcount = count($evals);
		if( $valcount <=2 ){
		$tmpelement = null;	
		for( $i = 0; $i < $valcount; $i++ ) {
			$element = '<input type="radio" ';
			$element .= 'name="'.$ename.'" ';
			$element .= 'value="'.$evals[$i].'" ';
			$element .= '/>'.ucwords($evals[$i]);
			$tmpelement .= $element;
		}
		return $this->divcapsulate($this->encodeinfieldset($tmpelement,$caption));	
		}else {
			$element = '<select name="'.$ename.'">';
			for( $i = 0; $i < $valcount; $i++ ) 
				$element .= '<option value="'.$evals[$i].'">'.ucwords($evals[$i]).'</option>';
			$element .= '</select>';
			return $this->divcapsulate($this->encodeinfieldset($element,$caption));
		}
		
	}
	
	private function createBigTextElement( $ename, $evalue, $caption='Title' ) {
		$element = '<textarea ';
		$element .= 'name="'.$ename.'">';
		$element .= $evalue;
		$element .= '</textarea>';
		return $this->divcapsulate($this->encodeinfieldset($element,$caption));
	}
	
	private function createPasswordElement( $ename, $evalue, $caption='Title' ) {
		$element = '<input type="password" ';
		$element .= 'name="'.$ename.'" ';
		$element .= 'value="'.$evalue.'" ';
		$element .= '/>';
		return $this->divcapsulate($this->encodeinfieldset($element,$caption));	
	}
	
	private function createRelElement ( $ename, $evals, $caption='Title') {
	$valcount = count($evals);
		if( $valcount <=2 ){
		$tmpelement = null;	
		for( $i = 0; $i < $valcount; $i++ ) {
			$element = '<input type="checkbox" ';
			$element .= 'name="'.$ename.'" ';
			$element .= 'value="'.$evals[$i].'" ';
			$element .= '/>'.ucwords($evals[$i]);
			$tmpelement .= $element;
		}
		return $this->divcapsulate($this->encodeinfieldset($tmpelement,$caption));	
		}else {
			$element = '<select name="'.$ename.'" multiselect size="3">';
			for( $i = 0; $i < $valcount; $i++ ) 
				$element .= '<option value="'.$evals[$i].'">'.ucwords($evals[$i]).'</option>';
			$element .= '</select>';
			return $this->divcapsulate($this->encodeinfieldset($element,$caption));
		}
	}
	
	private function createCalElement( $ename, $caption='Title') {
		$element = '<div>'.$caption.'</div>
		<div>
			<script>DateInput("'.$ename.'", false, "YYYY-MM-DD", "1985-12-11")</script>
			</div>';
		return $this->divcapsulate($element);
		
	}
	
	private function createImgElement( $ename, $evalue, $caption='Title') {
		$element = '';
		$element = '<script type="text/JavaScript">
		$(document).ready(function(){
			$(".pc").click(function(){
				$(".via-pc").fadeIn();
				$(".via-url").fadeOut();
				$(".url").removeClass("active");
				$(this).addClass("active");	
			});
		
			$(".url").click(function(){
				$(".via-url").fadeIn();
				$(".via-pc").fadeOut();
				$(".pc").removeClass("active");
				$(this).addClass("active");	
			});
		
		});
		
		 </script> ';
		
		$element .= '<div class="upload">
		<div class="pc-url"><a class="pc" href="javascript:void(0)">Upload via PC</a> <a class="url active" href="javascript:void(0)">Upload via URL</a></div>
		<div class="via-pc" style="display:none;"><label>Upload via PC</label><input type="file" name="file-'.$ename.'" /></div>
		<div class="via-url"><label>Upload via URL</label><input type="text" name="link-'.$ename.'" /></div>
		</div>';
		
		return $this->divcapsulate($this->encodeinfieldset($element,$caption));
	}

	 
	
	private function renderFormTags() {
		
		$renderData = null;
	
		$formElements = $this->formelem;
		foreach ( $formElements as $formElement => $formElementProps ) {
			switch ( strtolower(trim($formElementProps['type']))) {
				
				case 'smalltext':
					$ename = $formElement;
					$eval = $formElementProps['value'];
					$renderData .= $this->createSmallTextElement( $ename, $eval, $formElementProps['caption'] ); 
					break;
					
				case 'enum':
					$ename = $formElement;
					$eval = explode("|",$formElementProps['value']);
					$renderData .= $this->createEnumElement( $ename, $eval, $formElementProps['caption'] );
					break;
					
				case 'bigtext':
					$ename = $formElement;
					$eval = $formElementProps['value'];
					$renderData .= $this->createBigTextElement( $ename, $eval, $formElementProps['caption'] );
					break;
					
				case 'password':
					$ename = $formElement;
					$eval = $formElementProps['value'];
					$renderData .= $this->createPasswordElement( $ename, $eval, $formElementProps['caption'] );
					break;
					
				case 'rel':
					$ename = $formElement;
					$eval = explode("|",$formElementProps['value']);
					$renderData .= $this->createRelElement( $ename, $eval, $formElementProps['caption'] );
					break;
					
				case 'img':
					$ename = $formElement;
					$eval = $formElementProps['value'];
					$renderData .= $this->createImgElement( $ename, $eval, $formElementProps['caption'] );
					break;
	
				case 'cal':
					$ename = $formElement;
					$eval = $formElementProps['value'];
					$renderData .= $this->createCalElement( $ename, $formElementProps['caption'] );
					break;	
				
			}
			
			
			
		}
		return $renderData.'<li><input type="submit" name="add-data" value="Done"/></li>';;	
			
	}
	
	public function __toString() {
		return $this->renderFormTags();
		
	}
	
}