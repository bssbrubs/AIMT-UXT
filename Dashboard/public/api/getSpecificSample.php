<?php
$method = $_SERVER['REQUEST_METHOD'];
$jsonURL = $_GET["url"];
$handle = file_get_contents($jsonURL.'\\' . 'trace.json');
$str = ltrim($handle, ',');
$kolp = "[".$str."]";
echo $kolp;

?>