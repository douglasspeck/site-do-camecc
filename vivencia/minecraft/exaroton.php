<?php

ini_set('display_errors', '1');
ini_set('display_startup_errors', '1');
error_reporting(E_ALL);

$API_KEY = "rwgNDP5JYzAEtksdhDj1Q8rIRhkBwnJaOM4CMd47X1itojnW5oMVPnN7KmuSGO3G7PAqWJ2Il57RNM1Nsh9G79hfoesR0DaLtamq";

$url = "https://api.exaroton.com/v1/servers/";

/* REFERENCIAS

-> https://stackoverflow.com/questions/5647461/how-do-i-send-a-post-request-with-php
-> https://developers.exaroton.com/#servers-get
-> https://support.exaroton.com/hc/en-us/articles/360019857878-Using-the-exaroton-API 

*/

$options = [
    'http' => [
        'header' => "Authorization: $API_KEY\r\n",
        'method' => 'GET'
    ],
];
$context = stream_context_create($options);
$result = file_get_contents($url, false, $context);

var_dump($result);

?>