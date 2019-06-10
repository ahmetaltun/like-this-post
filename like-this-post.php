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
define("tableName", "ltp_likes");

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
    
    if(is_user_logged_in()){
        // get post id
        $postId = get_the_ID();
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
    }else{
        return $content;
    }
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

// ajax
function ltpAddLike(){
    if(DOING_AJAX){
        global $wpdb;

        $tableName = $wpdb->prefix.tableName;

        $getData = [
            "postId"=>$_REQUEST['postId'],
            "userId"=>$_REQUEST['userId']
        ];

        // postmeta
        $postMeta = get_post_meta($getData['postId'], 'ltpLikeCount', true); 

        // check
        $checkRow = $wpdb->get_results("select id,status from $tableName where postId=".$getData['postId']." and userId=".$getData['userId'].";");
        if(count($checkRow)==0){
            // insert
            $wpdb->query("insert into $tableName (postId,userId,status,createdAt) values (".$getData['postId'].",".$getData['userId'].",1,'".current_time('Y-m-d H:i:s')."');");
            $postMeta++;
            $status = 1;
        }else if(count($checkRow)>0 && $checkRow[0]->status==0){
            // change to like from unlike
            $wpdb->query("update $tableName set status=1 where id=".$checkRow[0]->id.";");
            $postMeta++;
            $status = 1;
        }else if(count($checkRow)>0 && $checkRow[0]->status==1){
            // change to unlike from like
            $wpdb->query("update $tableName set status=0 where id=".$checkRow[0]->id.";");
            $postMeta--;
            $status = 0;
        }

        // update postmeta
        update_post_meta($getData['postId'], 'ltpLikeCount', $postMeta);

        // print result
        print_r(json_encode(array("status"=>$status, "postId"=>$getData['postId'], "likeCount"=>$postMeta)));

        die();
    }
}
// end ajax

// widget

class ltpLikeWidget extends WP_Widget {

	public function __construct() {
        $widget_ops = array(
            'classname' => 'ltpLikeWidget',
            'description' => 'A widget for like this post plugin. List most liked posts.',
        );
        parent::__construct('ltpLikeWidget', 'like this post widget', $widget_ops);
    }
	
    public function widget($args, $instance) {
        echo $args['before_widget'];
        if (!empty( $instance['title'])) {
            print $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        $posts = get_posts(array(
            'posts_per_page' => '10',
            'meta_key'   => 'ltpLikeCount',
            'order' => 'desc',
            'orderby' => 'meta_value'
		));

        if(!empty($posts) && is_array($posts)){ 
            ?>
            <ul>
            <?php foreach ($posts as $post) { ?>
                <li><a href="<?php print get_permalink( $post->ID ); ?>">
                <?php print $post->post_title; ?> - ♡ <?php print get_post_meta($post->ID, 'ltpLikeCount', true); ?>
                </a></li>		
            <?php } ?>
            </ul>
            <?php
            
        }else{
            echo esc_html__('Empty here!', 'text_domain' );	
        }

        echo $args['after_widget'];
    }

    public function form($instance) {
        $title = ! empty($instance['title']) ? $instance['title'] : esc_html__('Title', 'text_domain');

        ?>
        <p>
        <label for="<?php echo esc_attr($this->get_field_id('title')); ?>">
        <?php esc_attr_e('Title:', 'text_domain'); ?>
        </label> 
        
        <input 
            class="widefat" 
            id="<?php echo esc_attr($this->get_field_id('title')); ?>" 
            name="<?php echo esc_attr($this->get_field_name('title')); ?>" 
            type="text" 
            value="<?php echo esc_attr($title); ?>">
        </p>
        <?php
    }

    public function update($new_instance, $old_instance) {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

add_action('widgets_init', function(){
	register_widget('ltpLikeWidget');
});

// end widget

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