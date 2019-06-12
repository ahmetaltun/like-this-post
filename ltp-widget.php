<?php

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

?>