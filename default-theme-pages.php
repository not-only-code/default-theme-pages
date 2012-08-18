<?php
/*
Plugin Name: Default theme pages
Plugin URI: https://github.com/not-only-code/default-theme-pages
Description: adds unremovable default pages for templating themes
Version: 0.3
Author: Carlos Sanz García
Author URI: http://codingsomething.wordpress.com/
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/


////////////////////////////////////////////////////////////////////////////////////////


if ( !function_exists('_debug') ):
function _debug( $message ) {
	   
	if ( WP_DEBUG === true ):
		 
		if ( is_array( $message ) || is_object( $message ) ) {
			
			error_log( print_r( $message, true ) );
			
		} else {
			
			error_log( $message );
		}
			 
	 endif;
}
endif;
  

////////////////////////////////////////////////////////////////////////////////////////


/**
 * Define Constants
 *
 * @since 0.1
 */
if (!defined("DTP_VERSION")) 		define("DTP_VERSION", '0.3');
if (!defined("DTP_PREFIX")) 		define("DTP_PREFIX", '_dtp_');
//if (!defined("DTP_PAGE_BASENAME")) 	define('DTP_PAGE_BASENAME', 'default-theme-pages-settings');
if (!defined("DTP_OPTIONS_NAME")) 	define("DTP_OPTIONS_NAME", 'dtp_options');
if (!defined("PHP_EOL")) 			define("PHP_EOL", "\r\n");


////////////////////////////////////////////////////////////////////////////////////////



/**
 * install default pages and associate it to an db option, needs global $default_theme_pages
 *
 * @package Default Theme Pages
 *
 * @since 0.1
**/
function dtp_template_loader( $template ) {
	global $default_theme_pages, $post;
	
	if (!$post) return $template;
	
	if ( $default_page_name = is_default_page($post) ) 
		if ($template_ = locate_template( "page-$default_page_name.php"))
			$template = $template_;
	
	return $template;
	

}
add_filter( 'template_include', 'dtp_template_loader', 0 );

	


/**
 * install default pages and associate it to an db option, needs global $default_theme_pages
 *
 * @package Default Theme Pages
 *
 * @since 0.3
**/
function dtp_is_page($option = false) {
	
	if ($page_id = dtp_get_page_id($option))
		if (is_page($page_id))
			return true;
	return;
}

function dtp_get_page_id($option = false) {
	global $default_theme_pages;
	
	if (!$option) return;
	
	$page_id = false;
	if (!isset($default_theme_pages[$option]))
		foreach ($default_theme_pages as $page) {
			if (isset($page['option'])) {
				if ($page['option'] === $option) $page_id = $page['id'];
			} else {
				if ($page['name'] === $option) $page_id = $page['id'];
			}
		}
	else
		$page_id = $default_theme_pages[$option];
	
	if ($page_id) return $page_id;
	
	return;
}

/**
 * install default pages and associate it to an db option, needs global $default_theme_pages
 *
 * @package Default Theme Pages
 *
 * @since 0.1
**/
function dtp_install_pages() {
	global $default_theme_pages;
	
	// get options
	$theme_pages = get_option('default_theme_pages');
	if (!$theme_pages) $theme_pages = array();
	wp_cache_set('default_theme_pages', $theme_pages);
	
	if ( !isset($default_theme_pages) || empty($default_theme_pages) ) return;
	
	// start out with basic page parameters, modify as we go
	$page_data = array(
		'post_status' => 'publish',
		'post_type' => 'page',
		'post_author' => 1,
		'post_name' => '',
		'post_title' => '',
		'post_content' => '',
		'comment_status' => 'closed'
	);
	
	foreach ($default_theme_pages as $index => $page):
		
		$page_data['post_title'] = $page['title'];
		if ( isset($page['content']) && $page['content'] != '' ) $page_data['post_content'] = $page['content'];
		
		$page_option =  isset($page['option']) ? $page['option'] : false;
		$page_id = dtp_create_single_page( $page['name'], $page_data, $page_option );
		
		if ($page_id) $default_theme_pages[$index]['id'] = $page_id;
		
	endforeach;
	
	// store all default page ids
	update_option('default_theme_pages', wp_cache_get('default_theme_pages'));
}
add_action( 'after_setup_theme', 'dtp_install_pages');



/**
 * Install a single page if required
 *
 * @param string $page_slug - is the slug for the page to create (shop|cart|thank-you|etc)
 * @param string $page_option - the database options entry for page ID storage
 * @param array $page_data - preset default parameters for creating the page - this will finish the slug
 *
 * @package Default Theme Pages
 *
 * @since 0.1
 */
function dtp_create_single_page( $page_slug, $page_data, $page_option = false ) {
    global $wpdb;
	
	$default_theme_pages = wp_cache_get('default_theme_pages');

    $slug = $page_slug;
	
	if ( $page_option ) {
		$page_options_id = get_option( $page_option );
	} else {
		$page_options_id = isset($default_theme_pages[$page_slug]) ? $default_theme_pages[$page_slug] : '';
	}
	
	if ( $page_options_id != "" )
		$page_found = get_page( $page_options_id );
	
	if ( !isset($page_found) || !$page_found )
		$page_found = get_page_by_path($page_slug);
		
	if ( !isset($page_found) || !$page_found ) {
		
		$create_page = true;
		$page_data['post_name'] = $page_slug;
		$page_options_id = wp_insert_post( $page_data );
		
		dtp_store_option($page_options_id, $page_slug, $page_option);
		
		return $page_options_id;
		
	} else {
		
		if ( $page_options_id == "" )
			dtp_store_option($page_found->ID, $page_found->post_name, $page_option );
		
		return $page_found->ID;
	}
	
	return;
}


/**
 * detects if is a default page
 *
 * @package Default Theme Pages
 *
 * @since 0.3
 *
**/
function dtp_store_option($page_id = false, $page_slug = '', $page_option = false) {
	
	if ( $page_slug === '' || !$page_id ) return;
	
	if ( $page_option ) {
		update_option( $page_option, $page_id ); 
	} else {
		$default_theme_pages = wp_cache_get('default_theme_pages');
		$default_theme_pages[$page_slug] = $page_id;
		wp_cache_set('default_theme_pages', $default_theme_pages);
	}
}



/**
 * detects if is a default page
 *
 * @package Default Theme Pages
 *
 * @since 0.1
 *
**/
function is_default_page($page = false) {
	global $default_theme_pages;
	
	if ( !$page || !$default_theme_pages ) return;
	
	if ( !is_object($page) )
		$page = get_page($page);
	
	if ( $page->post_type != 'page' ) return;
	
	foreach ( $default_theme_pages as $default_page )
		if ( $default_page['id'] == $page->ID )
			return $default_page['name'];
	
	return false;
}



/**
 * prevent change 'publish' status from any default page, maintains 'password'
 *
 * @package Default Theme Pages
 *
 * @since 0.2
 *
**/
function dtp_check_status_page( $post_id, $post_after, $post_before ) {
	global $wpdb, $default_theme_pages;
	
	if ( is_default_page($post_after) && $post_after->post_status != 'publish' )
		wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
 }
add_action('post_updated', 'dtp_check_status_page', 640, 3);



/**
 * prevent move to trash any default page
 *
 * @package Default Theme Pages
 *
 * @since 0.2
 *
**/
function dtp_trashed_post($post_id = false) {
	if ( !$post_id ) return;
	
	if ( is_default_page($post_id) )
		wp_update_post(array('ID' => $post_id, 'post_status' => 'publish'));
}
add_action('trashed_post', 'dtp_trashed_post', 640);



/**
 * adds image thumbnail, to project, slider lists
 *
 * @package Default Theme Pages
 *
 * @since 0.1
 *
**/
function dtp_page_columns($columns) {
	
	$comments_icon = $columns['comments'];
	
	unset($columns['comments']);
	unset($columns['author']);
	unset($columns['date']);
	
	$columns['blocked'] = "<img src=\"" . plugins_url( 'assets/images/padlock-icon.png' , __FILE__ ) . "\" width=\"22\" height=\"22\" />";
	$columns['comments'] = $comments_icon;
	$columns['date'] = __('Date');
	
	return $columns;
}
add_filter('manage_edit-page_columns', 'dtp_page_columns');



/**
 * adds gear icon to mark blocked pages
 *
 * @package Default Theme Pages
 *
 * @since 0.1
 *
**/
function dtp_page_show_columns($name) {
	global $post, $default_theme_pages;
	
	if (!isset($default_theme_pages)) return;
	
	switch ($name) {
		case 'blocked':
			foreach ($default_theme_pages as $page) {
				if ( $page['id'] == $post->ID ) {
					echo "<img src=\"" . plugins_url( 'assets/images/gear-icon.png' , __FILE__ ) . "\" width=\"19\" height=\"19\" /><br /><small style=\"color: gray\">" . $page['description'] . "</small>";
				}
			}
			break;
	}
}
add_filter('manage_page_posts_custom_column',  'dtp_page_show_columns');



/**
 * disable trash button on page publish meta box
 *
 * @package Default Theme Pages
 *
 * @since 0.2
 *
**/
function dtp_remove_delete_link() {
	global $pagenow, $post;
	
	if (!$post) return;
	
	if ( $pagenow == 'post.php' && is_default_page($post) ) {
		echo "<!-- DTP remove delete link -->" . PHP_EOL;
		echo "<style type=\"text/css\" media=\"screen\">" . PHP_EOL;
		echo "	#misc-publishing-actions > .misc-pub-section:first-child, #delete-action { display: none !important}" . PHP_EOL;
		echo "</style>" . PHP_EOL;
	}
}
add_action( 'admin_head', 'dtp_remove_delete_link', 900 );



/**
 * disable trash button on page row actions
 *
 * @package Default Theme Pages
 *
 * @since 0.2
 *
**/
function dtp_page_row_actions($actions, $post) {
	
	if ( is_default_page($post) )
		unset($actions['trash']);
	
	return $actions;
}
add_filter('page_row_actions', 'dtp_page_row_actions', 0, 2);



/**
 * some columns admin styles
 *
 * @package Default Theme Pages
 *
 * @since 0.1
 *
**/
function dtp_tables_styles() {
	echo "
	<style type=\"text/css\">
		.blocked.column-blocked { text-align: center;	}
		.manage-column.column-blocked { 
			width: 120px;
			text-align: center;
		}
	</style>
	".PHP_EOL;
}
add_filter('admin_head', 'dtp_tables_styles', 640);