<?php
/*
Copyright 2010  Matthew B. Jordan  (matthewbjordan.me)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See license.txt in the mjlk-link-tracking folder, or go to http://www.gnu.org/licenses/gpl-2.0.html
*/
include_once '../../../wp-config.php';
include_once '../../../wp-load.php';
include_once '../../../wp-includes/wp-db.php';

/**
  Custom XSS and MySQL cleaning functions.
*/
function xss($input, $strip = false) {
    $find = array('cookie', 'document.', 'window.', 'script ');
    $replace = array('milk', '_doc_', '_win_', 'scpt ');
    if ($strip) $input = strip_tags($input);
    else $input = htmlspecialchars($input, ENT_QUOTES);
    $input = str_replace($find, $replace, $input);
    return $input;
}
function cs($input) {
    $input = strip_tags($input);
    return str_replace(array("'", " OR ", "IS NULL", "COUNT(*)", " LIKE %", "%", "DROP TABLE", "INSERT INTO", "UPDATE ", "SET ", "TRUNCATE", "*"), array("&#39;", "", "", "", "", "&#37;", "", "", "", "", "", "&#42;"), $input);
}

$link = cs(str_replace("&", "&amp;", urldecode(base64_decode($_GET['re']))));
$notes = cs(htmlentities(base64_decode($_GET['n']), ENT_QUOTES, "UTF-8"));
$ref = ($_SERVER['HTTP_REFERER']) ? cs($_SERVER['HTTP_REFERER']) : 'Referer is null';
$ip = ($_SERVER['REMOTE_ADDR']) ? cs($_SERVER['REMOTE_ADDR']) : 'Remote IP is null';
$ref = str_replace("&","&amp;",$ref);

$insSQL = "INSERT INTO `".$table_prefix."mjlink` (`ldate`,`linkto`,`referer`,`ip`,`notes`) VALUES (NOW( ),'".base64_encode($link)."','$ref','$ip','".urldecode($notes)."');";
$wpdb->query($insSQL);

$link = xss($link);

// Catch any error and ouptut a backup-link
@header("Location: ".str_replace("&amp;","&",$link)) or die('<h1>Link Error</h1>Continue to <a href="'.$link.'">'.str_replace("&amp;","&",$link).'</a>');

?>