<?php
/**
Plugin Name:  MJLK Link Tracking
Plugin URI: http://matthewbjordan.me/mjlk
Description: Track inbound and outbound links
Version: 1.2
Author: Matthew B. Jordan
Author URI: http://matthewbjordan.me/
*//*
--------------------------------------------------------------------
Copyright 2010  Matthew B. Jordan  (matthewbjordan.me)

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License, version 2, as 
published by the Free Software Foundation.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

See license.txt in the mjlk folder, or go to http://www.gnu.org/licenses/gpl-2.0.html
*/
add_action('init','mjlk_init');
register_activation_hook(__FILE__,'mjlk_install');
register_deactivation_hook(__FILE__,'mjlk_uninstall');
wp_enqueue_script('mjlkjs', WP_PLUGIN_URL.'/mjlk-link-tracking/mjlk.js','','1.0');

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

/**
  Init function for future add-ons
*/
function mjlk_init() {
    add_action('admin_menu', 'mjlk_config_page');
}

function mjlk_installed() {
    global $wpdb, $table_prefix;
    $install = $wpdb->get_var("SHOW TABLES LIKE '".$table_prefix."mjlink'");
    if ($install === NULL) return false;
    else return true;
}

function mjlk_install() {
    global $table_prefix, $wpdb, $user_level;
    get_currentuserinfo();
    if (!mjlk_installed()) {
        $setupSQL = "CREATE TABLE IF NOT EXISTS `".$table_prefix."mjlink` (
  `ldate` datetime NOT NULL,
  `linkto` varchar(1024) collate utf8_unicode_ci NOT NULL,
  `referer` varchar(512) collate utf8_unicode_ci NOT NULL default 'None',
  `ip` varchar(32) collate utf8_unicode_ci NOT NULL default 'None',
  `notes` varchar(256) collate utf8_unicode_ci NOT NULL default 'None',
  KEY `linkto` (`linkto`(333))
) ENGINE=MyISAM DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;";
        $wpdb->query($setupSQL);
    }
}

/**
  Shortcode Function
*/
function mjlksw($atts, $content = null) {
    extract(shortcode_atts(array("href"=>'http://', "notes"=>'None'), $atts));
    return '<a href="'.$href.'" onmousedown="mjlk(this,\''.$notes.'\')">'.$content.'</a>';
}
add_shortcode('mjlk', 'mjlksw');

	
function mjlk_config_page() {
    if (function_exists('add_submenu_page')) {
        add_menu_page('Link Tracking', 'View Links', 8, 'mjlk', 'mjlk_view');
        add_submenu_page('mjlk', 'Clear Links', 'Clear all links', 'manage_options', 'mjlk_clearAll', 'mjlk_clear');
    }
}

function mjlk_view() {
    global $table_prefix, $wpdb;
    if (isset($_GET['jurl'])) {
        echo '<h1>Detail View</h1>';
        $classVar = htmlentities($_GET['jurl'], ENT_QUOTES, "UTF-8");
        $detail = $wpdb->get_results("SELECT `ldate`,`linkto`,`referer`,`ip`,`notes` FROM `".$table_prefix."mjlink` WHERE `linkto`='".cs($classVar)."' ORDER BY `ldate` DESC");
        foreach($detail as $det) {
            echo '<div style="background-color:#F8F8FF;color:000; border:#666 1px solid; padding:8px; margin:5px;">
<small><strong>Date:</strong> '.$det->ldate.'</small>
<p><strong>Link To:</strong> '.base64_decode($det->linkto).'</p>
<p><strong>IP Address:</strong> ';
            if ($det->ip != "None") {
                echo '<a href="http://whois.arin.net/rest/ip/'.$det->ip.'" target="_blank">'.$det->ip.'</a>';
            } else {
                echo $det->ip;
            }
            echo '</p>
<p><strong>Link From:</strong> '.$det->referer.'</p>
<p><small><strong>NOTES:</strong> '.$det->notes.'</small></p>
</div>';
        }
        echo '<a href="?page=mjlk&amp;delete='.$det->linkto.'">Delete This List</a> | <a href="?page=mjlk">Back to Main View</a>';
    } else {
        echo "<h1>Link View</h1>";
        if (isset($_GET['delete'])) {
            $classDel = htmlentities($_GET['delete'], ENT_QUOTES, "UTF-8");
            $oka = $wpdb->query("DELETE FROM `".$table_prefix."mjlink` WHERE `".$table_prefix."mjlink`.`linkto`  = '".cs($classDel)."'");
            $okb = $wpdb->query('OPTIMIZE TABLE `'.$table_prefix."mjlink".'`');
            if ($oka == FALSE || $okb == FALSE) echo '<div id="error" class="error"><p />There was an error deleting the links...</div>';
            else
            echo '<div id="message" class="updated fade"><p />All links: "'.base64_decode($_GET['delete']).'" were deleted</div>';
        }
        $show = $wpdb->get_results("SELECT DISTINCT `linkto` FROM `".$table_prefix."mjlink` ORDER BY `ldate` DESC");
        if (count($show) == 0) {
            echo '<h3>No Data To Display</h3>
Have you created a tracking link?<br />';
        } else {
            foreach($show as $vw) {
                $count_lks = $wpdb->query("SELECT `linkto` FROM `".$table_prefix."mjlink` WHERE `linkto` = '".cs($vw->linkto)."'");
                echo '<div style="width:90%;border:#999 1px solid;background-color:#F8F8FF;height:15px;padding:8px">
  <div style="float:left">
    <strong>Link:</strong>
    <a href="'.xss(base64_decode($vw->linkto)).'" target="_blank">'.xss(base64_decode($vw->linkto)).'</a>
  </div>
  <div align="right" style="float:right">
		<small><a>'.$count_lks.' Clicks</a> |</small>
    <a href="?page=mjlk&amp;jurl='.xss($vw->linkto).'"><small>details</small></a>
  </div>
</div>
<hr size="1" style="clear:both" />';
            }
        }
        echo '<p>To creat a tracking link, insert the following code into any post</p>
<p><code>[mjlk href=&quot;LINK&quot; notes=&quot;NOTE&quot;]DISPLAY TEXT[/mjlk]</code></p>
<h4>Attributes:</h4>
<p><code>LINK (required):</code> The full URL to the page you wish to link to.</p>
<p><code>NOTE (optional):</code> Use this Attribute to diferentiate between links, i.e. &quot;top of myPost&quot; and &quot;bottom of myPost&quot;</p>
<p><code>DISPLAY TEXT (required):</code> The text that will be diplayed in the users borwser, i.e. &quot;click Here&quot;</p>';
    }
}

/**
  Clear the links from the DB Table
*/
function mjlk_clear() {
    global $table_prefix, $wpdb;
    if (isset($_GET['clear'])) {
        $wpdb->query("TRUNCATE TABLE ".$table_prefix."`mjlink`");
        echo '<div id="message" class="updated fade"><p />Links Cleared - <a href="?page=mjlk">Done</a></div>';
    } else {
        echo '<h1>Clear Links</h1>Are you sure you want to clear all links?
<a href="?page=mjlk_clearAll&clear=true">Yes</a> / <a href="?page=mjlk">No</a><br />
<small>This action cannot be undone</small>';
    }
}

function mjlk_uninstall() {
    global $table_prefix, $wpdb;
    $wpdb->query('DROP TABLE `'.$table_prefix.'mjlink`');
}
?>