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
define("tableName", "ltp_likes");

require_once('func.php');
require_once('ltp-widget.php');
require_once('ltp-pages.php');
require_once('ltp-ajax.php');

function getCssFile(){
    $cssPath = cssFolder . '/css.css';
    print "<link rel='stylesheet' id='like-this-post-css' href='$cssPath' />";
}

function getJsFile() {
    $jsPath = jsFolder . '/js.js';
    print "<script type='text/javascript' src='$jsPath'></script>";
}


function likeButton($content){
    
    if(is_user_logged_in()){
        // get post id
        $postId = get_the_ID();
        // check post id
        if($postId>0){
            $userId = get_current_user_id();

            // get likes
            $likeCount = get_post_meta($postId, 'ltpLikeCount', true);

            // check user liked or not
            global $wpdb;
            $tableName = $wpdb->prefix.tableName;
            $currentUser = $wpdb->get_results("select id,status from $tableName where postId=$postId and userId=$userId;");
            if(count($currentUser)>0 && $currentUser[0]->status==1){
                $btnClass = 'likeButtonLike';
            }else{
                $btnClass = 'likeButtonIdle';
            }
            return $content .= '
                <div id="ltp-container">
                    <button id="ltp-like-button" class="'.$btnClass.'" data-post-id="'.$postId.'" data-user-id="'.$userId.'"></button>
                    <span id="ltp-like-count-box-'.$postId.'" class="ltp-like-count-box">'.$likeCount.'</span>
                </div>
            ';
        }else{ return $content; }
    }else{ return $content; }
}

function createSqlTable() {
    global $wpdb;

    $tableName = $wpdb->prefix.tableName;
    if($wpdb->get_var("show tables like '".$tableName."';") != $tableName){
        $query = "create table ".$tableName." (
                    `id` bigint(20) NOT NULL AUTO_INCREMENT,
                    `postId` bigint(20) NOT NULL,
                    `userId` bigint(20) NOT NULL,
                    `status` int(1) NOT NULL,
                    `createdAt` datetime NOT NULL,
                    PRIMARY KEY (`id`));
                ";
        $wpdb->query($query);
    }
}

function dropSqlTable() {
    global $wpdb;

    $tableName = $wpdb->prefix.tableName;
    $query = "drop table ".$tableName.";";
    $wpdb->query($query);
}

function init(){
    
}

register_activation_hook(__FILE__, 'createSqlTable');
//register_deactivation_hook( __FILE__, 'dropSqlTable');
register_uninstall_hook(__FILE__, 'dropSqlTable');

// add css file to between head tags
add_filter('wp_head', 'getCssFile');
//add_filter('wp_footer', 'getJsFile');

// add like button to content bottom
add_filter('the_content', 'likeButton');

// when plugin loaded, call init func
add_action('plugins_loaded', 'init');

// add js file to footer
wp_enqueue_script( 'ltp', jsFolder . '/js.js', array( 'jquery' ), false, true );
// set global variables
wp_localize_script( 'ltp', 'ltp_params', array(
    'ajaxUrl' => ajaxPostUrl
));

?>