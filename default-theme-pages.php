<?php
/*
Plugin Name: Default theme pages
Plugin URI: https://github.com/not-only-code/default-theme-pages
Description: adds unremovable default pages for templating themes
Version: 0.2
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
if (!defined("DTP_VERSION")) 		define("DTP_VERSION", '0.2');
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
function dtp_install_pages() {
	global $default_theme_pages;
	
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
	
	foreach ($default_theme_pages as $page):
		
		$page_data['post_title'] = $page['title'];
		if ( isset($page['content']) && $page['content'] != '' ) $page_data['post_content'] = $page['content'];
		dtp_create_single_page( $page['name'], $page['option'], $page_data );
		
	endforeach;
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
function dtp_create_single_page( $page_slug, $page_option, $page_data ) {
    global $wpdb;

    $slug = $page_slug;
	
	$page_options_id = get_option( $page_option );
	
	if ( $page_options_id != "" )
		$page_found = get_page( $page_options_id );
	
	if ( !$page_found )
		$page_found = get_page_by_path($page_slug);
		
	if ( !$page_found ) {
		
		$create_page = true;
		$page_data['post_name'] = $page_slug;
		$page_options_id = wp_insert_post( $page_data );
		update_option( $page_option, $page_options_id );
		
	} else {
		
		if ( $page_options_id == "" )
			update_option( $page_option, $page_found->ID );
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
	
	if ( !$page ) return;
	
	if ( !is_object($page) )
		$page = get_page($page);
	
	if ( $page->post_type != 'page' ) return;
	
	foreach ( $default_theme_pages as $default_page )
		if ( get_option($default_page['option']) == $page->ID )
			return true;
	
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
	
	switch ($name) {
		case 'blocked':
			foreach ($default_theme_pages as $page) {
				if ( get_option($page['option']) == $post->ID ) {
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