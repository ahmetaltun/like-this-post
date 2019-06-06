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
define("ajaxPostUrl", plugins_url('',__FILE__) . '/ajax.php');

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

function createSqlTable() {
    global $wpdb;

    $tableName = $wpdb->prefix . 'ltp_likes';
    if($wpdb->get_var("show tables like '$tableName';") != $tableName){
        $query = "create table $tableName (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `postId` bigint(20) NOT NULL,
                    `ip` varchar(60) NOT NULL,
                    `userId` bigint(20) NOT NULL,
                    `status` int(1) NOT NULL,
                    `createdAt` datetime NOT NULL,
                    PRIMARY KEY (`id`));
                ";
        /*
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';
        dbDelta( $query );
        */
        $wpdb->query($query);
    }
}

function dropSqlTable() {
    global $wpdb;

    $tableName = $wpdb->prefix . 'ltp_likes';
    $query = "drop table $tableName;";
    $wpdb->query($query);
}

register_activation_hook(__FILE__, 'createSqlTable');
register_deactivation_hook( __FILE__, 'dropSqlTable');

add_filter('wp_head', 'getCssFile');
//add_filter('wp_footer', 'getJsFile');
add_filter('the_content', 'likeButton');


wp_enqueue_script( 'ltp', jsFolder . '/js.js', array( 'jquery' ), false, true );
wp_localize_script( 'ltp', 'ltp_params', array(
    'ajaxUrl' => ajaxPostUrl
));

?>