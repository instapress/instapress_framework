<?php


class Instapress_Core_TextCompare
{
	/*
	 * usage:
	 * $objTextCompare = new Instapress_Core_TextCompare();
	 * $result = $objTextCompare->textCompare($text1, $text2);
	 * print_r($result);
	 */

	public function textCompare($text1, $text2)
	{
		require_once(LIB_PATH. 'Instapress/Core/TextCompare/finediff.php');

		$diff = new FineDiff($text1, $text2, FineDiff::$wordGranularity);
		$edits = $diff->getOps();
		$rendered_from_diff = htmlspecialchars_decode($diff->renderFromDiffToHTML(), ENT_NOQUOTES);
		$rendered_to_diff = htmlspecialchars_decode($diff->renderToDiffToHTML(), ENT_NOQUOTES);

		return array("oldtext"=>$rendered_to_diff, "newtext"=>$rendered_from_diff);
	}
}