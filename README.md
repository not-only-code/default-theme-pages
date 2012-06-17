Default Theme Pages
===================

Adds unremovable default pages for templating themes.

How to use
----------

Add this code in your *functions.php*.

	global $default_theme_pages;
	$default_theme_pages = array(
	 	array(
	 		'name' => 'home', 				// slug page
	 		'title' => 'Homepage', 			// page title
	 		'option' => 'page_on_front',	// option name
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
	 		'option' => 'mytheme_contact_page',
	 		'description' => 'Contact form page'
	 	)
	 );


Trick
-----

If you use `page_on_front` and `page_for_posts` option names, you will block the *home-page* and the *blog-page* on your site.
