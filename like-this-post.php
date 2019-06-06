<?php
/*
@package    like-this-post
@author     ahmet ALTUN
@link       https://github.com/ahmetaltun

Plugin Name: Like This Post
Plugin URI: https://github.com/ahmetaltun
Description: Add like button to each post
Version: 0.0.1
Author: Ahmet ALTUN
Author URI: https://ahmetaltun.com.tr
*/

define("appDir", plugins_url('', __FILE__));
define("incFolder", appDir . '/inc');
define("cssFolder", incFolder . '/css');
define("jsFolder", incFolder . '/js');

function getCssFile(){
    $cssPath = cssFolder . '/css.css';
    print "<link rel='stylesheet' id='like-this-post-css' href='$cssPath' />";
}

function getJsFile() {
    $jsPath = jsFolder . '/js.js';
    print "<script type='text/javascript' src='$jsPath'></script>";
}


function likeButton($content){
    $postId = get_the_ID();
    
    return $content .= '
        <div id="ltp-container">
            <button id="ltp-like-button" data-post-id="'.$postId.'"></button>
            <span id="ltp-like-count-box">0</span>
        </div>
    ';
}

function createTable() {

}

register_activation_hook(__FILE__, 'createTable');
add_filter('wp_head', 'getCssFile');
add_filter('wp_footer', 'getJsFile');
add_filter('the_content', 'likeButton');


?>