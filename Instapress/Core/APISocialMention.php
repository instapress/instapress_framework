<?php


class Instapress_Core_APISocialMention
{
	function __construct($query)
	{		
		$ch = curl_init("http://api2.socialmention.com/search?$query");
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt ($ch, CURLOPT_TIMEOUT, 60);
		$curlResult = curl_exec($ch);
		curl_close($ch);
		
		$dataArray = json_decode($curlResult);

		echo "totalCount=".$totalCount = count($dataArray);echo "<br>";
		print_r($dataArray);echo "<br><br><br><br><br>";

		
		echo "Count Array element=". $totalCount = $dataArray->count;
		echo "<br><br><br><br><br>";
		
		
		for($i=0; $i<10; $i++)
		{			
			echo "title=".$dataArray->items[$i]->title;echo "<br>";
			echo "description=".$dataArray->items[$i]->description;echo "<br>";
			echo "link=".$dataArray->items[$i]->link;echo "<br>";
			echo "timestamp=".$dataArray->items[$i]->timestamp;echo "<br>";
			echo "image=".$dataArray->items[$i]->image;echo "<br>";
			echo "embed=".$dataArray->items[$i]->embed;echo "<br>";
			echo "user=".$dataArray->items[$i]->user;echo "<br>";
			echo "user_image=".$dataArray->items[$i]->user_image;echo "<br>";
			echo "user_link=".$dataArray->items[$i]->user_link;echo "<br>";
			echo "user_id=".$dataArray->items[$i]->user_id;echo "<br>";
			echo "source=".$dataArray->items[$i]->source;echo "<br>";
			echo "favicon=".$dataArray->items[$i]->favicon;echo "<br>";
			echo "type=".$dataArray->items[$i]->type;echo "<br>";
			echo "domain=".$dataArray->items[$i]->domain;echo "<br>";
			echo "id=".$dataArray->items[$i]->id;echo "<br>";echo "<br>";echo "<hr>";echo "<br>";
		}
	}
}