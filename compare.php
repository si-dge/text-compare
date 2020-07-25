<?php

$start = time();

$content = [];
$is = [0,1,2,3,4,5,6,7,8,9,10,11,12];
$add = false;

	foreach ($is as $find) {

		$find_file = getFile($find);
		$find_file_lenght = mb_strlen($find_file, 'UTF-8');

		foreach ($is as $i) {
			$file = getFile($i);
			$file_lenght = mb_strlen($find_file, 'UTF-8');

			$compare = compare($file,$find_file);
			$result = ($compare/$find_file_lenght*100);

			$compare_result = $result > 69; // && $result < 100

			if($compare_result) {

				$result_array = [$find,$i];
				$result_array = array_unique($result_array);
				if(count($result_array) > 1) {
					if(empty($content)) $content[] = $result_array;
					foreach ($result_array as $result_array_key => $result_array_value) {
						foreach ($content as $content_key => $content_array) {
							echo "\n\n";
							if(in_array($result_array_value, $content_array)) {
								$content[$content_key] = array_merge($content[$content_key], $result_array);
								$content[$content_key] = array_unique($content[$content_key]);
							} else {
								$add = true;
							}
							echo "\n\n";
						}
						if($add) {
							$content[] = $result_array;
							$add = false;
						}
					}
				}
			}
		}
	}

// }

$end = time();

print_r($content);
print_r("\n");
print_r($end-$start);
print_r(" sec \n");

function getFileText($i)
{
	$filepath = __DIR__.'/articles/'.$i.'';
	$file = file_get_contents($filepath);

	return $file;
}

function getFile($i)
{
	$filepath = __DIR__.'/articles/'.$i.'';
	$file = file_get_contents($filepath);
	$file = prepareText($file);
	$file = implode(' ', $file);

	return $file;
}

function removeStopWords($orig_words)
{
	$words = [];
	$filepath = __DIR__.'/stops_ru.txt';
	$file = file_get_contents($filepath);
	$stop_words = explode("\n", $file);
	$stop_words = array_unique($stop_words);
	foreach ($orig_words as $word) {
		if(!in_array($word, $stop_words)) $words[] = $word;
	}
	return $words;
}

function prepareText(string $text)
{	
	clearText($text);
	$words = explodeTextToWords($text);
	$words = removeStopWords($words);
	$words = wordsToHash($words);
	return $words;
}

function clearText(string &$text)
{
	$text = trim($text);
	$text = mb_strtolower($text);
	$text = str_replace([".", ",", ":", ";", "!", "?", '«', '»', '"', '“', '(', ')', '[', ']', '%', '/', '\\', ' '], '', $text);
	$text = str_replace('ё', 'е', $text);
	$text = str_replace(['а','у','о','ы','и','э','я','ю','ё','е','a','e','i','o','u', 'ь','ъ'], '', $text);
	$text = str_replace(['  ','   ','   '], ' ', $text);
	return $text;
}

function explodeTextToWords(string $text)
{
	$words = explode(' ', $text);
	foreach ($words as $key => &$word) {
		$word = trim($word);
		$word_lenght = mb_strlen($word, 'UTF-8');
		// if( $word_lenght <= 4 ) {
			// unset($words[$key]);
		// }
	}
	return $words;
}

function wordToDigit(string $word)
{
	$original_word = $word;
	// рrлйlбпфвbfpvтдdtжшщчсцзкгхcgjkqsxzмнmn
	// 664441111111133337777222222222222225555
	$word = str_replace(['0','1','2','3','4','5','6','7','8','9'], '9', $word);
	$word = str_replace(['р','r'], '6', $word);
	$word = str_replace(['л','й','l','б'], '4', $word);
	$word = str_replace(['п','ф','в','b','f','p','v','т'], '1', $word);
	$word = str_replace(['д','d','t','ж'], '3', $word);
	$word = str_replace(['ш','щ','ч','с'], '7', $word);
	$word = str_replace(['ц','з','к','г','х','c','g','j','k','q','s','x','z','м'], '2', $word);
	$word = str_replace(['н','m','n'], '5', $word);
	$word = str_replace('-', '', $word);

	$word_lenght = mb_strlen($word, 'UTF-8');
	$crop_lenght = 6;
	$word = substr($word, 0, $crop_lenght);
	for ($i=$crop_lenght; $i < $word_lenght; $i++) { 
		$word .= 8;
	}

	return $word;
}

function wordsToHash(array $words)
{
	$hash_words = [];
	foreach ($words as $key => $word) {
		$code_word = wordToDigit($word);
		$hash_words[$word] = $code_word;
	}
	sort($hash_words);
	return $hash_words;
	return $words;
}

function compare(string $similar, string $find)
{
	$check[] = $check1 = similar_text($similar, $find, $perc1);
	$check[] = $check2 = similar_text($find, $similar, $perc2);

	return ($check1 > $check2) ? $check1 : $check2;
}