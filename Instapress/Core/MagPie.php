<?php


require_once 'MagPie/rss_fetch.inc';
class MagPie
{
	function getRssContents( $rssUrl ) {
		$rss = fetch_rss( $rssUrl );
		//describe( $rss );
		
		if(!$rss)
			return false;

		$channelInfo = array();
		$channelInfo[ 'title' ] = trim( $rss->channel[ 'title' ] );		
		$channelInfo[ 'description' ] = trim( $rss->channel[ 'description' ] );
		$channelInfo[ 'link' ] = trim( $rss->channel[ 'link' ] );

		$channelInfo[ 'last_modified' ] = date( "Y-m-d H:i:s" );
		if( isset( $rss->last_modified ) ) {
			$channelInfo[ 'last_modified' ] = date( 'Y-m-d H:i:s', strtotime( $rss->last_modified ) );
		} else if( isset( $rss->channel[ 'pubdate' ] ) ) {
			$channelInfo[ 'last_modified' ] = date( 'Y-m-d H:i:s', strtotime( $rss->channel[ 'pubdate' ] ) );
		} else if( isset( $rss->channel[ 'lastbuilddate' ] ) ) {
			$channelInfo[ 'last_modified' ] = date( 'Y-m-d H:i:s', strtotime( $rss->channel[ 'lastbuilddate' ] ) );
		} else if( isset( $rss->items[ 0 ][ 'pubdate' ] ) ) {
			$channelInfo[ 'last_modified' ] = date( 'Y-m-d H:i:s', strtotime( $rss->items[ 0 ][ 'pubdate' ] ) );
		} else if( isset( $rss->items[ 0 ][ 'dc' ][ 'date' ] ) ) {
			$channelInfo[ 'last_modified' ] = date( 'Y-m-d H:i:s', strtotime( $rss->items[ 0 ][ 'dc' ][ 'date' ] ) );
		}
		
		$retArray = array();
		$itemIndex = 0;
		foreach( $rss->items as $item ) {
			$href = $item['feedburner']['origlink'];
			if( $href =='' AND ( isset( $item[ 'link' ] ) ) )
				$href = $item[ 'link' ];
			if( $href =='' AND (isset( $item[ 'guid' ] ) ) )
				$href = $item[ 'guid' ];
			
			$href =  trim( $href );
			$title =  trim( $item['title'] );

			if( $href != "" && $title != '' )
			{
				//describe( $currentNewsLink );
				//describe( $href );
				//echo "\n";

				$date = $item['published'];
				if ($date =='') $date = $item['updated'];
				if ($date =='') $date = $item['dc']['date'];
				if ($date =='') $date = $item['lastBuildDate'];
				if ($date =='') $date = $item['pubdate'];
				if ($date =='') $date = $item['pubDate'];				
				
				$temp_date=date("r",strtotime($date));				
				
				date_default_timezone_set("GMT");
				$temp_date1=date("Y-m-d H:i:s",strtotime($temp_date));
				date_default_timezone_set("GMT");

				if($temp_date1=='1969-12-31 19:00:00' or $temp_date1=='1970-01-01 00:00:00' or $temp_date1=='0000-00-00 00:00:00')
					$temp_date1=date("Y-m-d H:i:s");				

				if( strtotime( $temp_date1 ) > time() ) {
					break;
				}

				$retArray[ $itemIndex ][ 'publish_date' ] = $temp_date1;

				if($item['guid'])
				{
					$retArray[ $itemIndex ][ 'guid' ] = trim( $item['guid'] );
				}
				else
					$retArray[ $itemIndex ][ 'guid' ] = "";
				
				$retArray[ $itemIndex ][ 'link' ] = $href;

				$retArray[ $itemIndex ][ 'title' ] = strip_tags($title);

				$desc = $item['description'];
				if ($desc =='') $desc = $item['summary'];
				if ($desc =='') $desc = $item['atom_content'];
				
				$desc_arr=explode("{}}()",$desc);
				$count_desc_arr = count($desc_arr);
				if($count_desc_arr>1)
					$desc=trim($desc_arr[1]);					

				$retArray[ $itemIndex ][ 'description' ] = $item['content']['encoded'];
				
				$temp_unprocessed_content = "";
				$temp_unprocessed_content=trim( $item['content']['encoded'] );
	
				$imageSearchContent = $temp_unprocessed_content. $desc;
				
				preg_match( '/<img[^>]+>/', $imageSearchContent, $matches );

				$imageUrls = array();
				if( count( $matches ) > 0 ) {
					$imageUrls = array_unique( $this->getImageUrls( $matches ) );
				}
				else
				{
					//check for media url link in rss
				}
				
				$retArray[ $itemIndex ][ 'image_urls' ] = $imageUrls;

				$itemIndex++;
			}
		}
		return array( 'items' => $retArray, 'channel' => $channelInfo );
	}

	function getImageUrls( $imgTags ) {
		$images = array();
		foreach( $imgTags as $tag ) {
			$tag = explode( 'src=', $tag );
			if( count( $tag ) < 2 ) {
				continue;
			}
			$tag = explode( ' ', $tag[ 1 ] );
			$imageUrl = trim( $tag[ 0 ], '\'">/' );
			$imageUrl = str_replace(" ", "%20", $imageUrl);
			$images[] = $imageUrl;
		}
		return $images;
	}
}
