<?php

class Instapress_Core_InstamediaPlagiarism {

    private $_querywords = 0;
    private $_matchedResultsCount = 0;
    private $_matchedResults = array();

    public function __construct($text_to_search) {
        $this->checkForStoryPlagairism($text_to_search);
    }

    public function getResultCount() {
        return $this->_matchedResultsCount = count($this->_matchedResults);
    }

    public function getResults() {
        return $this->_matchedResults;
    }

    private function checkForStoryPlagairism($text = FALSE) {
        $text = strip_tags(stripslashes($text));
        if ($text) {
            try {
                //echo $text;
            	$plagArray = json_decode($this->plagiarism($text));
                //print_r($plagArray);
                $i = 0;
                if ($plagArray) {
                    foreach ($plagArray->matchdata as $plagData) {
                        $this->_matchedResults[$i]['index'] = $i + 1;
                        $this->_matchedResults[$i]['url'] = $plagData->url;
                        $this->_matchedResults[$i]['domain'] = $plagData->domain;
                        $this->_matchedResults[$i]['title'] = stripslashes($plagData->title);
                        $this->_matchedResults[$i]['percentage'] = $plagData->percentage;
                        $this->_matchedResults[$i]['shortsnippet'] = substr(stripslashes($plagData->textsnippet), 0, 150);
                        $this->_matchedResults[$i]['textsnippet'] = stripslashes($plagData->textsnippet);

                        if ($this->_matchedResults[$i]['percentage'] > 50)
                            $this->_matchedResults[$i]['color'] = 'red';
                        else if ($this->_matchedResults[$i]['percentage'] > 20)
                            $this->_matchedResults[$i]['color'] = 'yellow';
                        else
                            $this->_matchedResults[$i]['color'] = 'green';

                        $i++;
                    }
                }
            } catch (Exception $ex) {
                throw $ex;
            }
        }
    }

    private function plagiarism($text) {
        //if (isset($_REQUEST['text'])) {
        //  $text = urldecode($_REQUEST['text']);
        $data = trim($text);
        $percentage = 0;
        $wordsData = explode(" ", $data);
        $words_count = count($wordsData);
        $matchdata[] = array();
        $index = 0;

        if ($words_count < 6) {
            //echo "0";
        } else {
            $j = 0;
            for ($i = 0; $i < $words_count; $i++) {

                if ($i % 16 == 0) {
                    $data16[$j] = substr($data16[$j], 1);
                    $j++;
                }
                $data16[$j] = $data16[$j] . ' ' . $wordsData[$i];
            }
            $data16[$j] = substr($data16[$j], 1);

            $j++;

            $numOf16Blocks = $j;
            $oneWordPlag = 100 / $words_count;
            for ($j = 0; $j < $numOf16Blocks; $j++) {

                if ($data16[$j] == "")
                    continue;
                if ($j == $numOf16Blocks - 1) {
                    $wordsData16 = explode(" ", $data16[$j]);
                    $wordsinblockcount = count($wordsData16);
                } else {
                    $wordsinblockcount = 16;
                }
                $datatosearch = '"' . $data16[$j] . '"';

                $datatosearch = urlencode($datatosearch);


                $url = 'http://api.search.live.net/json.aspx?Appid=C9C6F4DA9DBD8C9C246B8455FE0070B2B7D4C45F&query=' . $datatosearch . '&sources=web&web.Count=10&web.Offset=0';

                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                curl_setopt($ch, CURLOPT_REFERER, "http://www.bing.com/");
                $body = curl_exec($ch);
                curl_close($ch);
                $json = json_decode($body);


                if ($json->SearchResponse->Web->Results) {

                    $matchdata[$index]['textsnippet'] = $data16[$j];
                    $url = $json->SearchResponse->Web->Results[0]->Url;
                    $matchdata[$index]['url'] = $url;
                    $domain = parse_url($url, PHP_URL_HOST);
                    $domain = str_replace("www.", "", $domain);
                    $matchdata[$index]['domain'] = $domain;
                    $matchdata[$index]['percentage'] = number_format($oneWordPlag * $wordsinblockcount, 2);

                    $index++;
                    foreach ($json->SearchResponse->Web->Results as $jsonresult) {


                        $urldata[$jsonresult->Url]['textsnippet'] .=$data16[$j];
                        $urldata[$jsonresult->Url]['percentage'] += number_format($oneWordPlag * $wordsinblockcount, 2);
                        $urldata[$jsonresult->Url]['title'] = $jsonresult->Title;
                    }
                    $percentage+= ( $oneWordPlag * $wordsinblockcount);
                } else {



                    $wordsData8 = explode(" ", $data16[$j]);
                    $words_count8 = count($wordsData8);
                    $k = 0;
                    $data8 = Array();
                    for ($z = 0; $z < $words_count8; $z++) {
                        if ($z % 8 == 0) {

                            $data8[$k] = substr($data8[$k], 1);
                            $k++;
                        }
                        $data8[$k] = $data8[$k] . ' ' . $wordsData8[$z];
                    }
                    $data8[$k] = substr($data8[$k], 1);

                    $k++;
                    $numOf8Blocks = $k;
                    for ($y = 0; $y < $numOf8Blocks; $y++) {
                        if ($data8[$y] == "")
                            continue;
                        if ($j == $numOf8Blocks - 1) {
                            $wordsData8 = explode(" ", $data8[$y]);
                            $wordsinblockcount8 = count($wordsData8);
                        } else {
                            $wordsinblockcount8 = 8;
                        }


                        $datatosearch8 = '"' . $data8[$y] . '"';
                        $datatosearch8 = urlencode($datatosearch8);

                        $url = 'http://api.search.live.net/json.aspx?Appid=C9C6F4DA9DBD8C9C246B8455FE0070B2B7D4C45F&query=' . $datatosearch8 . '&sources=web&web.Count=10&web.Offset=0';
                        $ch = curl_init();
                        curl_setopt($ch, CURLOPT_URL, $url);
                        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
                        $body = curl_exec($ch);
                        curl_close($ch);
                        $json8 = json_decode($body);


                        if ($json8->SearchResponse->Web->Results) {

                            $matchdata[$index]['textsnippet'] = $data8[$y];
                            $url = $json8->SearchResponse->Web->Results[0]->Url;
                            $matchdata[$index]['url'] = $url;
                            $domain = parse_url($url, PHP_URL_HOST);
                            $domain = str_replace("www.", "", $domain);
                            $matchdata[$index]['domain'] = $domain;
                            $matchdata[$index]['percentage'] = number_format($oneWordPlag * $wordsinblockcount8, 2);
                            $index++;

                            foreach ($json8->SearchResponse->Web->Results as $jsonresult8) {

                                $jsonresult8->Url;
                                $urldata[$jsonresult8->Url]['textsnippet'] .=$data16[$j];
                                $urldata[$jsonresult8->Url]['percentage'] += number_format($oneWordPlag * $wordsinblockcount8, 2);
                                $urldata[$jsonresult8->Url]['title'] = $jsonresult8->Title;
                            }
                            $percentage+= ( $oneWordPlag * $wordsinblockcount8) / 2;
                        }
                    }
                }
            }
        }

        $matchdata = array();
        $index = 0;
        foreach ($urldata as $key => $value) {

            $matchdata[$index]['textsnippet'] = $value['textsnippet'];
            $url = $key;
            $matchdata[$index]['url'] = $url;
            $domain = parse_url($url, PHP_URL_HOST);
            $domain = str_replace("www.", "", $domain);
            $matchdata[$index]['domain'] = $domain;
            $matchdata[$index]['percentage'] = $value['percentage'];
            $matchdata[$index]['title'] = $value['title'];

            $index++;
        }

        $source["percentage"] = number_format($percentage, 2);
        $source["matchdata"] = $matchdata;
        return json_encode($source);
    }

}

?>
