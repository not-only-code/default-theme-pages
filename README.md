Default Theme Pages
===================

Adds unremovable default pages for templating themes.

New in version 0.3
------------------

* changed stored system to gain performance
* added some accessible functions: `dtp_is_page`, `dtp_get_page_id`
* added default pages in template system, now you can template a default page as: `page-name.php`

How to use
----------

Add this code in your *functions.php*.

	global $default_theme_pages;
	$default_theme_pages = array(
	 	array(
	 		'name' => 'home',				// initial slug page / id for templating - access functions
	 		'title' => 'Homepage',			// page title
	 		'option' => 'page_on_front',	// option page id ( stores the page id in an option, use only in theese cases)
	 		'description' => 'homepage'		// description
	 	),
	 	array(
	 		'name' => 'blog',
	 		'title' => 'Blog',
	 		'option' => 'page_for_posts',
	 		'description' => 'blog'
	 	),
	 	array(
	 		'name' => 'contact',
	 		'title' => 'Contact me!',
	 		'description' => 'Contact form page'
	 	)
	 );

Tips
----

*After the pages are created automatically, you can change their title and slug without problem.
*If you add an `option` names: `page_on_front` and `page_for_posts`, you will block the *home-page* and the *blog-page* on your site.


Templating
----------

You can template using **ID** or **name** (setted up in $default_theme_pages global variable ), something like:

`page-131.php` or `page-contact.php` (If you change the page slug, this will still work because 'contact' was defined as *pagename* if $default_theme_pages global variable).

For detect if you're in that page:

`dtp_is_page('contact')`

Getting a default page id:

`$page_id = dtp_get_page_id('contact');` This method is more quick than *get_page_by_path* because page ID is stored on global variable.


Changelog
---------

**0.3**  
* changed stored system to gain performance
* added some accessible functions: `dtp_is_page`, `dtp_get_page_id`
* added default pages in template system, now you can template a default page as: `page-name.php`

**0.2**    
* blocked status modifications
* blocked move to trash

**0.1**  
* Initial release