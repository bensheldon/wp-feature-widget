<?php
/*
Plugin Name: Featured Widget
Plugin URI: http://bostoncyclistunion.org
Description: Adds a widget for featured posts/pages
Author: Ben Sheldon
Version: 1
Author URI: http://island94.org
*/

// Slider from here:
// http://www.queness.com/post/1450/jquery-photo-slide-show-with-slick-caption-tutorial-revisited


/**
 * featuredWidget Class
 */
class featuredWidget extends WP_Widget {
    /** constructor */
    function featuredWidget() {
        parent::WP_Widget(false, $name = 'featuredWidget');
        
        if (is_active_widget(false, false, $this->id_base, true)) {
          // Add the Nivo Slider Stuff
          wp_enqueue_style('featuredWidget-nivo-style', WP_PLUGIN_URL . '/featuredwidget/nivo-slider/nivo-slider.css',false,'1.0','all');
          wp_enqueue_script('featuredWidget-nivo-script', WP_PLUGIN_URL . '/featuredwidget/nivo-slider/jquery.nivo.slider.pack.js',false,'1.0','all');
          
          // Add our plugin's stuff
          wp_enqueue_style('featuredWidget-style', WP_PLUGIN_URL . '/featuredwidget/featuredwidget.css',false,'1.0','all');
          wp_enqueue_script('featuredWidget-script', WP_PLUGIN_URL . '/featuredwidget/featuredwidget.js',false,'1.0','all');
          
        }
    }

    /** @see WP_Widget::widget */
    function widget($args, $instance) {		

    
        extract( $args );
        $title = apply_filters('widget_title', $instance['title']);
        ?>
              <?php echo $before_widget; ?>
                  <?php if ( $title )
                        echo $before_title . $title . $after_title; ?>
                  <?php $this->featuredWidgetContent() ?>
              <?php echo $after_widget; ?>
        <?php
              
    }

    /** @see WP_Widget::update */
    function update($new_instance, $old_instance) {				
			$instance = $old_instance;
			$instance['title'] = strip_tags($new_instance['title']);
      return $instance;
    }

    /** @see WP_Widget::form */
    function form($instance) {				
        $title = esc_attr($instance['title']);
        ?>
         <p>
          <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label> 
          <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" />
        </p>
        <?php 
    }
    
    /** Outputs the content **/
    function featuredWidgetContent() {

			$args = array( 
				'meta_key' => 'featuredWidget_display',
				'meta_value' => 'on',
				'post_type' => array( 'post', 'page' ),
				'number' => 12,
			);
	
			print '<div id="slider">';
			$query = New WP_Query($args);
			while( $query->have_posts() ) {
				$query->the_post();
				$image_id = get_post_thumbnail_id();  
				$image_url = wp_get_attachment_image_src($image_id, 'feature-front');  
				$image_url = $image_url[0];
				$image_caption = '<h3><a href=\'' . get_permalink() . '\'>' . get_the_title() .'</a></h3>';
				$image_caption .= str_replace('"','\'', get_post_meta(get_the_ID(), 'featuredWidget_description', true)); //replace double-quotes with single ones 
				$image_caption .= ' <strong><a href=\'' . get_permalink() . '\'>Learn&nbsp;more&nbsp;&raquo;</a></strong>';
				
				print	'<a href="' . get_permalink() . '">';
				print '<img src="' . $image_url . '" alt="' . get_the_title() . '" title="' . $image_caption . '"/></a>';
				print '</a>';
			}
			print ('</div>');
    }
    
} // class FooWidget

// register FooWidget widget
add_action('widgets_init', create_function('', 'return register_widget("featuredWidget");'));

if ( function_exists( 'add_image_size' ) ) { 
	add_image_size( 'feature-front', 593, 325, TRUE ); //300 pixels wide (and unlimited height)
}




/* Add settings to the Post */

/* Use the admin_menu action to define the custom boxes */
if (is_admin()) {
add_action('admin_menu', 'featuredWidget_add_custom_box');
}
/* Use the save_post action to do something with the data entered */
add_action('save_post', 'featuredWidget_save_postdata');

/* Use the publish_post action to do something with the data entered */
#add_action('publish_post', 'bfa_ata_save_postdata');

#add_action('pre_post_update', 'bfa_ata_save_postdata');

/* Adds a custom section to the "advanced" Post and Page edit screens */
function featuredWidget_add_custom_box() {

  if( function_exists( 'add_meta_box' )) {
    add_meta_box( 'featuredWidget_sectionid', __( 'Featured Widget Post Options', 'featuredWidget' ), 
                'featuredWidget_inner_custom_box', 'post', 'normal', 'high' );
    add_meta_box( 'featuredWidget_sectionid', __( 'Featured Widget Post Options', 'featuredWidget' ), 
                'featuredWidget_inner_custom_box', 'page', 'normal', 'high' );
  }
}
   
/* Prints the inner fields for the custom post/page section */
function featuredWidget_inner_custom_box() {

	global $post;
	
  // Use nonce for verification

  echo '<input type="hidden" name="featuredWidget_noncename" id="featuredWidget_noncename" value="' . wp_create_nonce( plugin_basename(__FILE__) ) . '" />';

  // The actual fields for data entry
  
  $thePostID = $post->ID;
	$post_id = get_post($thePostID);
	$title = $post_id->post_title;

	$featuredWidget_display = get_post_meta($post->ID, 'featuredWidget_display', true);
	$featuredWidget_description = get_post_meta($post->ID, 'featuredWidget_description', true);		

	echo '<table cellpadding="5" cellspacing="0" border="0" style="table-layout:fixed;width:100%">';
	echo '<tr><td style="text-align:right;padding:2px 5px 2px 2px"><input id="featuredWidget_display" name="featuredWidget_display" type="checkbox" '. ($featuredWidget_display == 'on' ? ' CHECKED' : '') .' /></td><td>Display as a Featured Item (don\'t forget to set a thumbnail)</td></tr>';

	echo '<tr><td style="text-align:right;vertical-align:top;padding:5px 5px 2px 2px"><label for="featuredWidget_description">' . __("Featured Description", 'featuredWidget' ) . '</label></td>';
	echo '<td><textarea name="featuredWidget_description" cols="70" rows="4" style="width:97%">'.$featuredWidget_description.'</textarea></td></tr>';
	
	echo '</table>';

}

/* When the post is saved, save our custom data */
function featuredWidget_save_postdata( $post_id ) {

  /* verify this came from the our screen and with proper authorization,
  because save_post can be triggered at other times */

  if ( !wp_verify_nonce( $_POST['featuredWidget_noncename'], plugin_basename(__FILE__) )) {
    return $post_id;
  }

  if ( 'page' == $_POST['post_type'] ) {
    if ( !current_user_can( 'edit_page', $post_id )) {
      return $post_id;
    }
  } else {
    if ( !current_user_can( 'edit_post', $post_id )) {
      return $post_id;
    }
  }

	// Save the data

	$new_featuredWidget_display = !isset($_POST["featuredWidget_display"]) ? NULL : $_POST["featuredWidget_display"];
	$new_featuredWidget_description = $_POST['featuredWidget_description'];

	update_post_meta($post_id, 'featuredWidget_display', $new_featuredWidget_display);
	update_post_meta($post_id, 'featuredWidget_description', $new_featuredWidget_description);

}
