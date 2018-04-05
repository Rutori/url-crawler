<?php

if (!empty($_POST)){
	include(realpath(".")."/lurker.php");
	$crawler = new lurker();
	$words = explode("|", $_POST['words']);
	foreach ($words as $key => $url) {
		$words[$key] = trim($url);
		if (empty($words[$key])){
			unset($words[$key]);
		}
	}
	var_dump($words);
	$crawler->add_url($words);
	$valid = $crawler->fetch();
	if ($valid){
		foreach ($valid as $url) {
			echo ("<a href='{$url}' target='_blank'>{$url}</a><br>");
		}
	} else{
		echo "Пусто";
	}
} else {
	echo file_get_contents(realpath(".")."/form.html");
}