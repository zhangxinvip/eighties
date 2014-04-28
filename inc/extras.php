<?php
/**
 * Custom functions that act independently of the theme templates
 *
 * Eventually, some of the functionality here could be replaced by core features
 *
 * @package Eighties
 * @author Justin Kopepasah
 * @since 1.0.0
 */

/**
 * Get our wp_nav_menu() fallback, wp_page_menu(), to show a home link.
 *
 * @param array $args Configuration arguments.
 * @return array
 */
function eighties_page_menu_args( $args ) {
	$args['show_home'] = true;
	return $args;
}
add_filter( 'wp_page_menu_args', 'eighties_page_menu_args' );

/**
 * Adds custom classes to the array of body classes.
 *
 * @param array $classes Classes for the body element.
 * @return array
 */
function eighties_body_classes( $classes ) {
	// Adds a class of group-blog to blogs with more than 1 published author.
	if ( is_multi_author() ) {
		$classes[] = 'group-blog';
	}

	return $classes;
}
add_filter( 'body_class', 'eighties_body_classes' );

/**
 * Filters wp_title to print a neat <title> tag based on what is being viewed.
 *
 * @param string $title Default title text for current view.
 * @param string $sep Optional separator.
 * @return string The filtered title.
 */
function eighties_wp_title( $title, $sep ) {
	if ( is_feed() ) {
		return $title;
	}
	
	global $page, $paged;

	// Add the blog name
	$title .= get_bloginfo( 'name', 'display' );

	// Add the blog description for the home/front page.
	$site_description = get_bloginfo( 'description', 'display' );
	if ( $site_description && ( is_home() || is_front_page() ) ) {
		$title .= " $sep $site_description";
	}

	// Add a page number if necessary:
	if ( $paged >= 2 || $page >= 2 ) {
		$title .= " $sep " . sprintf( __( 'Page %s', 'eighties' ), max( $paged, $page ) );
	}

	return $title;
}
add_filter( 'wp_title', 'eighties_wp_title', 10, 2 );

/**
 * Sets the authordata global when viewing an author archive.
 *
 * This provides backwards compatibility with
 * http://core.trac.wordpress.org/changeset/25574
 *
 * It removes the need to call the_post() and rewind_posts() in an author
 * template to print information about the author.
 *
 * @global WP_Query $wp_query WordPress Query object.
 * @return void
 */
function eighties_setup_author() {
	global $wp_query;

	if ( $wp_query->is_author() && isset( $wp_query->post ) ) {
		$GLOBALS['authordata'] = get_userdata( $wp_query->post->post_author );
	}
}
add_action( 'wp', 'eighties_setup_author' );

/**
 * Add search to the primary menu.
 *
 * @return string Navigation menu items.
*/
function eighties_primary_menu_items( $items, $args ) {
	if ( $args->theme_location != 'primary' ) {
		return $items;
	}

	ob_start();
	?>
		<li class="menu-item menu-item-search">
			<a href><i class="fa fa-search"></i></a>
			<form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
				<label>
					<span class="screen-reader-text"><?php _ex( 'Search for:', 'label', 'eighties' ); ?></span>
					<input type="search" class="search-field" placeholder="<?php echo esc_attr_x( 'Search &hellip;', 'placeholder', 'eighties' ); ?>" value="<?php echo esc_attr( get_search_query() ); ?>" name="s">
				</label>
			</form>
		</li>
	<?php
	$search = ob_get_clean();

	$items = $search . $items;

	return $items;
}
add_action( 'wp_nav_menu_items' , 'eighties_primary_menu_items', 100, 2 );

/**
 * Filter the excerpt length for archive, blog
 * and search.
 *
 * @param string $length The current length.
 * @return string $length The filterd length.
*/
function eighties_excerpt_length( $length ) {
	if ( is_home() || is_archive() || is_search() ) {
		return 45;
	} else {
		return $length;
	}
}
add_filter( 'excerpt_length', 'eighties_excerpt_length' );

/**
 * Filter the excerpt more for archive, blog
 * and search.
 *
 * @param string $more The current more text.
 * @return string $more The filterd more text.
*/
function eighties_excerpt_more( $more ) {
	if ( is_home() || is_archive() || is_search() ) {
		return '...';
	} else {
		return $more;
	}
}
add_filter( 'excerpt_more', 'eighties_excerpt_more' );

/**
 * Add the mobile menu close to the primary menu.
 *
 * @param string $items The current navigation items.
 * @param string $args The current navigation arguments.
 * @return string $items The modified navigation items.
*/
function eighties_wp_nav_menu_items( $items, $args ) {
	// If not the primary menu, return the items.
	if ( $args->theme_location != 'primary'  ) {
		return $items;
	}

	$close = '<li id="mobile-menu-close" class="menu-item menu-item-hidden"><a href><i class="fa fa-times"></i><span>' . __( 'Close', 'eighties' ) . '</span></a></li>';

	$close .= $items;

	$items = $close;

	return $items;
}
add_action( 'wp_nav_menu_items' , 'eighties_wp_nav_menu_items', 100, 2 );