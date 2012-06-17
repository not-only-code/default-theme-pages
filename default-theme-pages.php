<?php
/*
Plugin Name: Default theme pages
Plugin URI: https://github.com/not-only-code/default-theme-pages
Description: adds unremovable default pages for templating themes
Version: 0.1
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
if (!defined("DTP_VERSION")) 		define("DTP_VERSION", '0.1');
if (!defined("DTP_PREFIX")) 		define("DTP_PREFIX", '_dtp_');
//if (!defined("DTP_PAGE_BASENAME")) 	define('DTP_PAGE_BASENAME', 'default-theme-pages-settings');
if (!defined("DTP_OPTIONS_NAME")) 	define("DTP_OPTIONS_NAME", 'dtp_options');
if (!defined("PHP_EOL")) 			define("PHP_EOL", "\r\n");


////////////////////////////////////////////////////////////////////////////////////////



/**
  * Default pages
  *
 **/
 
 /*
 global $default_theme_pages;

$default_theme_pages = array(
 	array(
 		'name' => 'inicio',
 		'title' => 'Inicio',
 		'option' => 'page_on_front',
 		'description' => 'homepage'
 	),
 	array(
 		'name' => 'blog',
 		'title' => 'Blog',
 		'option' => 'page_for_posts',
 		'description' => 'blog'
 	),
 	array(
 		'name' => 'contacto',
 		'title' => 'Presupuesto',
 		'option' => 'thermia_budget_page_id',
 		'description' => 'pagina presupuesto'
 	),
 	array(
 		'name' => 'page-contacto',
 		'title' => 'Contacto',
 		'option' => 'thermia_contact_page_id',
 		'description' => 'pagina contacto'
 	),
 	array(
 		'name' => 'terminos-y-condiciones',
 		'title' => 'Términos y Condiciones',
 		'option' => 'thermia_terms_page_id',
 		'description' => 'pagina legales'
 	),
 	array(
 		'name' => 'proyectos',
 		'title' => 'Proyectos',
 		'option' => 'thermia_projects_page_id',
 		'description' => 'pagina proyectos'
 	),
 	array(
 		'name' => 'productos',
 		'title' => 'Productos',
 		'option' => 'thermia_products_page_id',
 		'description' => 'pagina productos'
 	)
 );
 */




/**
 * check if is some theme pages
 *
 * @package Default Theme Pages
 *
 * @version 0.1
**/
/*
function tm_is_contact_page() {
	
	if ( is_page(get_option('thermia_contact_page_id')) ) return true;
	
	return false;
}

function tm_is_budget_page() {
	
	if ( is_page(get_option('thermia_budget_page_id')) ) return true;
	
	return false;
}


function tm_is_projects_page() {
	
	if ( is_page(get_option('thermia_projects_page_id')) ) return true;
	
	return false;
}

function tm_is_products_page() {
	
	if ( is_page(get_option('thermia_products_page_id')) ) return true;
	
	return false;
}
*/



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
	$page_found = $wpdb->get_var("SELECT ID FROM " . $wpdb->posts . " WHERE post_name = '$slug' AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1");
	$page_options_id = get_option( $page_option );

    if ( ! $page_found ) {
		
		$create_page = true;
		if ( $page_options_id <> '' ) :
			$page_found = $wpdb->get_var( "SELECT ID FROM " . $wpdb->posts . " WHERE ID = '$page_options_id' AND post_status = 'publish' AND post_status <> 'trash' LIMIT 1" );
			if ( $page_found ) $create_page = false;
		endif;
		if ( $create_page ) :
			$page_data['post_name'] = $slug;
			$page_options_id = wp_insert_post( $page_data );
			update_option( $page_option, $page_options_id );
		endif;
		
    } else {
		
    	if ( $page_options_id == "" ) :
    		update_option( $page_option, $page_found );
    	else :
    		// we have the slug page, another page may be actual page in options (eg: 'shop|store|etc').
    		// Do we need to check for that page.
    	endif;
    }
}



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
					echo "<img src=\"" . plugins_url( 'assets/images/gear-icon.png' , __FILE__ ) . "\" width=\"22\" height=\"22\" /><br /><small>" . $page['description'] . "</small>";
				}
			}
			break;
	}
}
add_filter('manage_page_posts_custom_column',  'dtp_page_show_columns');



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
