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
//define("ajaxPostUrl", plugins_url('',__FILE__) . '/ajax.php');
define("ajaxPostUrl", admin_url('admin-ajax.php'));

require_once('func.php');

function getCssFile(){
    $cssPath = cssFolder . '/css.css';
    print "<link rel='stylesheet' id='like-this-post-css' href='$cssPath' />";
}

function getJsFile() {
    $jsPath = jsFolder . '/js.js';
    print "<script type='text/javascript' src='$jsPath'></script>";
}


function likeButton($content){
    // get post id
    $postId = get_the_ID();
    
    return $content .= '
        <div id="ltp-container">
            <button id="ltp-like-button" data-post-id="'.$postId.'" data-user-id="'.get_current_user_id().'"></button>
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

function init(){
    include(appDir . '/ajax.php');
}

// ajax
function ltpAddLike(){
    global $wpdb;
    
    $getData = [
        "postId"=>$_REQUEST['postId'],
        "userId"=>$_REQUEST['userId']
    ];
    /*
    $tableName = $wpdb->prefix . 'ltp_likes';
    $queryCheck = "select id from ".$tableName." where postId=".$_POST['postId']." and userId=".$_POST['userId']." and ip='".getIp()."';";
    print_r($queryCheck);
    */
    die();

}
// end ajax

register_activation_hook(__FILE__, 'createSqlTable');
register_deactivation_hook( __FILE__, 'dropSqlTable');

// add css file to between head tags
add_filter('wp_head', 'getCssFile');
//add_filter('wp_footer', 'getJsFile');

// add like button to content bottom
add_filter('the_content', 'likeButton');

// when plugin loaded, call init func
add_action('plugins_loaded', 'init');

// add functions for ajax call
add_action('wp_ajax_nopriv_ltpAddLike', 'ltpAddLike' );
add_action('wp_ajax_ltpAddLike', 'ltpAddLike' );

// add js file to footer
wp_enqueue_script( 'ltp', jsFolder . '/js.js', array( 'jquery' ), false, true );
// set global variables
wp_localize_script( 'ltp', 'ltp_params', array(
    'ajaxUrl' => ajaxPostUrl
));

?>