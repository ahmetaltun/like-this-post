<?php

// page

class Page {

    public function __construct() {
        add_filter('the_posts', array( $this, 'pages'));
    }

    private static function getPages() {
        // get tag count
        $tagCount = wp_count_terms('post_tag');

        // get tags
        $tags = get_tags(array(
            'number' => '30',
            'offset' => '0',
            'order' => 'desc',
            'orderby' => 'count'            
        ));
        if ($tags) {
            foreach($tags as $tag) {
                $Tag .= '<span id="ltp-tags">'.$tag->name.'<span>'.$tag->count.'</span></span>';
            }
        }else{
            $Tag = 'Tags not found.';
        }

        $pages['show-tags'] = array(
            'title'   => 'Tags',
            'content' => $Tag
        );

        return $pages;
    }

    public function pages($posts) {
        global $wp, $wp_query;
        $pages       = self::getPages();
        $pages_slug = array();
        foreach ($pages as $slug => $fp) {
            $pages_slugs[] = $slug;
        }
        if ( true === in_array(strtolower($wp->request), $pages_slugs)
             || ( true === isset($wp->query_vars['page_id'])
                  && true === in_array(strtolower($wp->query_vars['page_id']), $pages_slugs)
            )
        ) {
            if ( true === in_array(strtolower($wp->request), $pages_slugs)) {
                $page = strtolower($wp->request);
            } else {
                $page = strtolower($wp->query_vars['page_id']);
            }
            $posts = null;
            $posts[] = self::createPage($page, $pages[$page]);
            $wp_query->is_page = true;
            $wp_query->is_singular = true;
            $wp_query->is_home = false;
            $wp_query->is_archive = false;
            $wp_query->is_category = false;
            $wp_query->is_fake_page = true;
            $wp_query->fake_page = $wp->request;

            unset( $wp_query->query["error"] );
            $wp_query->query_vars["error"] = "";
            $wp_query->is_404 = false;
        }

        return $posts;
    }

    private static function createPage($pagename, $page) {
        $post = new stdClass;
        $post->post_author = 1;
        $post->post_name = $pagename;
        $post->guid = get_bloginfo('wpurl') . '/' . $pagename;
        $post->post_title = $page['title'];
        $post->post_content = $page['content'];
        $post->ID = - 1;
        $post->post_status = 'static';
        $post->comment_status = 'closed';
        $post->ping_status = 'closed';
        $post->comment_count = 0;
        $post->post_date = current_time('mysql');
        $post->post_date_gmt = current_time('mysql', 1);

        return $post;
    }
}

new Page();

// end page

?>