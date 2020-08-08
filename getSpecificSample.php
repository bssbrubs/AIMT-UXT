<?php
$jsonURL = "C:\\xampp\\htdocs\\webtracer\\Samples\\musicamazoncombr\\f1cade35fd238e618d50e32bf2b89d56deeb2d74970fb3abebbdba5b37840a6\\trace.json";

$handle = file_get_contents($jsonURL);
echo $handle;

?>