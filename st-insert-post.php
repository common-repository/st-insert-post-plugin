<?php
	/*
	  Plugin Name: ST Insert Post
	  Plugin URI: http://mystickypost.com/groups/wordpress/forum/topic/st-insert-post-plugin/
	  Version: 1.0.3
	  Author: Shayne Thiessen
	  Author URI: http://shaynethiessen.com/
	  Description: A simple front end post form for all users, and as a bonus the ability to list subpages.
	  Text Domain: st-insert-post
	  License: GPL2
	 */

	// Enable shortcodes in text widgets
	add_filter( 'widget_text', 'do_shortcode' );

	// Make plugin available for translation
	load_plugin_textdomain( 'st-insert-post', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );

	// Register stylesheet
	wp_register_style( 'st-insert-post', stip_plugin_url() . '/css/style.css', false, stip_get_version(), 'all' );

	// Enqueue stylesheet
	if ( !is_admin() )
		wp_enqueue_style( 'st-insert-post' );

	function stip_shortcode() {

		$return = '<div class="st-insert-post">';

		// Posting form
		if ( empty( $_POST['save'] ) ) {

			$dropdown_cats_args = array(
				'hide_empty' => 0,
				'echo' => 0,
				'show_count' => 0,
				'hierarchical' => 1,
				'name' => 'category'
			);

			$return .= '<form action="#stip-message" method="post" class="stip-form">';
			$return .= '<div class="stip-box"><span class="stip-label">' . __( 'Title', 'st-insert-post' ) . '</span><br><input type="text" name="title" size="40" /></div>';
			$return .= '<div class="stip-box"><br><span class="stip-label">' . __( 'Category', 'st-insert-post' ) . '</span><br>' . wp_dropdown_categories( $dropdown_cats_args ) . '</div>';
			$return .= '<div class="stip-box"><br><span class="stip-label">' . __( 'Content', 'st-insert-post' ) . '</span><br><textarea name="content" rows="7" cols="70"></textarea></div>';
			$return .= '<div class="stip-box"><br><input type="submit" value="' . __( 'Publish', 'st-insert-post' ) . '" /><br></div>';
			$return .= '<input type="hidden" name="save" value="true" />';
			$return .= '</form>';
		}

		// Data sent
		else {

			// Not specified title
			if ( empty( $_POST['title'] ) ) {
				$return .= '<div id="stip-message" class="stip-error">' . __( 'You need to specify a title! You will now be automatically sent back to do so.', 'st-insert-post' ) . '<br/></div>';
					// Redirect
					$return .= '
						<script type="text/javascript">
							setTimeout( "javascript:history.go(-1)", 6000 );
						</script>
					';
			}

			// Not specified content
			elseif ( empty( $_POST['content'] ) ) {
				$return .= '<div id="stip-message" class="stip-error">' . __( 'Oops, looks like you forgot to enter content! You will now be automatically sent back to do so.', 'st-insert-post' ) . '<br/></div>';
					// Redirect
					$return .= '
						<script type="text/javascript">
							setTimeout( "javascript:history.go(-1)", 6000 );
						</script>
					';
			}

			// Title and content specified
			else {

				// New post args
				$post_args = array(
					'post_title' => apply_filters( 'the_title', $_POST['title'] ), 
					'post_content' => apply_filters( 'the_content', $_POST['content'] ),
					'post_status' => 'pending', 
					'post_author' => $userID,
					'post_category' => array( $_POST['category'] )
				);

				// Post added
				if ( wp_insert_post( $post_args ) ) {

					// Sucessfull message
					$return .= '<div id="stip-message" class="stip-success">' . __( 'Entry successfull added! Please wait as you are redirected.', 'st-insert-post' ) . '</div>';

					// Redirect
					$return .= '
						<script type="text/javascript">
							setTimeout( "location.href = \'' . get_permalink() . '\';", 6000 );
						</script>
					';
				}
			}
		}

		$return .= '</div>';

		return $return;
	}
	add_shortcode( 'st_insert_post', 'stip_shortcode' );
	function stip_get_version() {
		if ( !function_exists( 'get_plugins' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}
		$plugin_folder = get_plugins( '/' . plugin_basename( dirname( __FILE__ ) ) );
		$plugin_file = basename( ( __FILE__ ) );
		return $plugin_folder[$plugin_file]['Version'];
	}

	function stip_plugin_url() {
		return plugins_url( basename( __FILE__, '.php' ), dirname( __FILE__ ) );
	}
	function stip_add_locale_strings() {
		$strings = __( 'Shayne Thiessen', 'st-insert-post' ) . __( 'Front-end posting form for guests. Usage: <code>[st_insert_post]</code>', 'st-insert-post' );
	}

function stls_generate($args = '') {
		
	$r = shortcode_atts( array(
		'depth'       => 0,
		'show_date'   => '',
		'date_format' => get_option('date_format'),
		'child_of'    => -1,
		'exclude'     => '',
		'echo'		  => 0,
		'title_li'    => '',
		'authors'     => '',
		'sort_column' => 'menu_order, post_title',
		'css_class'   => 'stls_list',
		'escape'	  => 'false'), $args );
	
	if ($r['escape'] != 'true') {
		global $wp_query;
		$postID = $wp_query->post->ID;	
		if ($r['child_of'] == -1) 	
			$r['child_of'] = $postID;
			
		$r['echo'] = 0;
		$r['title_li'] = '';

		$children = wp_list_pages($r);
		
		$content = '<ul class="'.$r['css_class'].'">'.wp_list_pages($r).'</ul>';
		
	} else {
		// escape is true, we want to show the shortcode instead of processing it
		$content = '[stls';
		foreach ($args as $key => $value) {
			if ($key != 'escape')
				$content .= ' '.$key.'="'.$value.'"';
		}
		$content .= ']';
	}
	
	return $content;	
}

add_shortcode('stls', 'stls_generate');
?>