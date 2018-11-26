<?php
//使用范例
include "GoogleTranslator.php";
$google = new GoogleTranslator();
$text = $google->getContent("今天天气不错");
var_dump($text);

