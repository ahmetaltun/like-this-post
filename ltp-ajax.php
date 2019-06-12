<?php

//define("ajaxPostUrl", plugins_url('',__FILE__) . '/ajax.php');
define("ajaxPostUrl", admin_url('admin-ajax.php'));

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

// add functions for ajax call
add_action('wp_ajax_nopriv_ltpAddLike', 'ltpAddLike' );
add_action('wp_ajax_ltpAddLike', 'ltpAddLike' );

?>