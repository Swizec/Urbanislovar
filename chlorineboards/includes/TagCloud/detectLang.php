<?php

/**
* Tag cloud creation class
*     @class		   detectLang
*     @author         http://boxoffice.ch/pseudo/index.php
*     @contact          
*     @version               0.1.0
* @filesource
*/

/**
* I just made this into a class for simpler use
*/

class detectLang
{
	function detectLang( $path )
	{
		$this->FINGERPRINT = $path . '/includes/TagCloud/fingerprints';
		
		//set the value of $max_delta to 80000 and/or reduce the number of
		//fingerprints in your directory if you want to speed up on the evaluation time
		//if you set the value of $max_delta too low, no language will be recognized
		$this->max_delta = 140000;  //(best evaluation is 140000 with original definitions)
	}
	
	//************* The 3 basic N-Gram functions  *********************************//
	function getFingerprint($dir, $nb_grams = 400) {
	    $pattern = "*.lm";
	    chdir($dir);
	    $files = glob($pattern);
	    foreach ($files as $readfile) {
	        if (is_file($readfile)) {
	            $bsnm = basename($readfile, ".lm");
	            $handle = fopen($readfile, 'r');
	            for ($i=0; $i < $nb_grams; $i++) {
	                $line = fgets($handle);
	                $part = explode(" ", $line);
	                $lm_ng[$bsnm][]= trim($part[0]);
	            }
	        }
	    }
	    return $lm_ng;
	}
	function createNGrams($string, $ng_number=350, $ng_max_chars=4) {
	    $array_words = explode(" ", $string);
	    //iterate over each word, each character, all possible n-grams
	    foreach($array_words as $word) {
	        $word = "_". $word . "_";
	        $length = strlen($word);
	        for ($pos=0; $pos < $length; $pos++){ //start position within word
	            for ($chars=0; $chars<$ng_max_chars; $chars++) { //length of ngram
	                if (($pos + $chars) < $length) { //if not beyond end of word
	                     $array_ngram[] = substr($word, $pos, $chars+1);
	                 }
	             }
	         }
	    }
	    //count-> value(frequency, int)... key(ngram, string)
	    $ng_frequency = array_count_values($array_ngram);
	    //sort array by value(frequency) desc
	    arsort($ng_frequency);
	    //use only top frequent ngrams
	    $most_frequent = array_slice($ng_frequency, 0, $ng_number);
	    foreach ($most_frequent as $ng => $number_frequencey){
	        $sub_ng[] = $ng;
	    }
	    return $sub_ng;
	}
	function compareNGrams($sub_ng, $lm_ng) {
	    foreach ($lm_ng as $lm_basename => $language) {
	        $delta = 0;
	        //compare each ngram of input text to current lm-array
	        foreach ($sub_ng as $key => $existing_ngram){
	            //match
	            if(in_array($existing_ngram, $language)) {
	                $delta += abs($key - array_search($existing_ngram, $language));
	            //no match
	            } else {
	                $delta += 400;
	            }
	            //abort: this language already differs too much
	            if ($delta > $this->max_delta) {
	                break;
	             }
	        } // End comparison with current language
	        //include only non-aborted languages in result array
	        if ($delta < ($this->max_delta - 400)) {
	            $result[$lm_basename] = $delta;
	        }
	    } //End comparison all languages
	    if(!isset($result)) {
	      $result = '';
	    } else {
	        asort($result);
	    }
	    return $result;
	}
	
	function detect( $string )
	{
		$string = stripslashes( $string );
		
		/* N-Gram Functions */
        $lm_ng = $this->getFingerprint($this->FINGERPRINT);
        $sub_ng = $this->createNGrams($string);
        $result_array = $this->compareNGrams($sub_ng, $lm_ng);
		//First item in result array is best matching language
		list($result, $point) = each($result_array);
		
		return $result;
	}
}


?>