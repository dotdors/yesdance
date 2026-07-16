<?php
/**
 * Functions.php
 *
 */

  //add page excerpts 
add_post_type_support( 'page', 'excerpt' );


add_filter( 'excerpt_length', function($length) {
    return 25;
}, PHP_INT_MAX );



 if ( ! function_exists( 'quark_posted_on' ) ) {
	function quark_posted_on() {
		
		// Translators: 1: Icon 2: Permalink 3: Post date and time 4: Publish date in ISO format 5: Post date
		$date = sprintf( '<a href="%2$s"  class="post-date" title="posted on %3$s" rel="bookmark"><time class="entry-date" datetime="%4$s" itemprop="datePublished">%5$s</time></a>',
			$post_icon,
			esc_url( get_permalink() ),
			sprintf( esc_html__( '%1$s @ %2$s', 'quark' ), esc_html( get_the_date() ), esc_attr( get_the_time() ) ),
			esc_attr( get_the_date( 'c' ) ),
			esc_html( get_the_date() )
		);

		// Translators: 1: Date link 2: Author link 3: Categories 4: No. of Comments
		$author = sprintf( '<address class="author vcard">by <a class=" url fn n" href="%1$s" title="%2$s" rel="author">%3$s</a></address>',
			esc_url( get_author_posts_url( get_the_author_meta( 'ID' ) ) ),
			esc_attr( sprintf( esc_html__( 'View all posts by %s', 'quark' ), get_the_author() ) ),
			get_the_author()
		);

		// Return the Categories as a list
		$categories_list = get_the_category_list( esc_html__( ' ', 'quark' ) );

		// Translators: 1: Permalink 2: Title 3: No. of Comments
		$comments = sprintf( '<span class="comments-link"><i class="fa fa-comment" aria-hidden="true"></i> <a href="%1$s" title="%2$s">%3$s</a></span>',
			esc_url( get_comments_link() ),
			esc_attr( esc_html__( 'Comment on ' , 'quark' ) . the_title_attribute( 'echo=0' ) ),
			( get_comments_number() > 0 ? sprintf( _n( '%1$s Comment', '%1$s Comments', get_comments_number(), 'quark' ), get_comments_number() ) : esc_html__( 'No Comments', 'quark' ) )
		);

		// Translators: 1: Date 2: Author 3: Categories 4: Comments
		printf( wp_kses( __( '<div class="header-meta">%1$s %2$s <div class="catlist">%3$s</div></div>', 'quark' ), array(
			'div' => array (
				'class' => array() ),
			'span' => array(
				'class' => array() ) ) ),
			$date,
			$author,
			$categories_list,
			( is_search() ? '' : $comments )
		);
	}
}
function remove_some_widgets(){

// Unregister some of the dsquark sidebars
unregister_sidebar( 'insta' );
unregister_sidebar( 'sidebar-int-wide' );
unregister_sidebar( 'sidebar-interior2' );
unregister_sidebar( 'sidebar-footer2' );
unregister_sidebar( 'sidebar-footer3' );

}
add_action( 'widgets_init', 'remove_some_widgets', 11 );
add_filter( 'testimonials_widget_image_size', 'my_testimonials_widget_image_size' );

function my_testimonials_widget_image_size( $size ) {
    $size                       = "medium";

    return $size;
}
add_filter( 'tw_image_size', 'my_tw_image_size' );

function my_tw_image_size( $size ) {
    $size                       = "medium";

    return $size;
}

//Page Slug Body Class
function add_slug_body_class( $classes ) {
global $post;
if ( isset( $post ) ) {
$classes[] = $post->post_type . '-' . $post->post_name;
}
return $classes;
}
add_filter( 'body_class', 'add_slug_body_class' );



/**  USE SVGS Overrides fontawesome
 * Return an unordered list of linked social media icons, based on the urls provided in the Theme Options
 *
 * @since Quark 1.0
 *
 * @return string Unordered list of linked social media icons
 */
if ( ! function_exists( 'quark_get_social_media' ) ) {
	function quark_get_social_media() {
		$output = '';
		$icons = array(
			array( 'url' => of_get_option( 'social_twitter', '' ), 'icon' => 'svg-twitter', 'title' => esc_html__( 'Follow us on Twitter', 'quark' ) ),
			array( 'url' => of_get_option( 'social_facebook', '' ), 'icon' => 'svg-facebook', 'title' => esc_html__( 'Friend us on Facebook', 'quark' ) ),
			array( 'url' => of_get_option( 'social_googleplus', '' ), 'icon' => 'svg-social1', 'title' => esc_html__( 'Connect with us on Google+', 'quark' ) ),
			array( 'url' => of_get_option( 'social_linkedin', '' ), 'icon' => 'svg-linkedin', 'title' => esc_html__( 'Connect with us on LinkedIn', 'quark' ) ),
			array( 'url' => of_get_option( 'social_slideshare', '' ), 'icon' => 'svg-slideshare', 'title' => esc_html__( 'Follow us on SlideShare', 'quark' ) ),
		array( 'url' => of_get_option( 'social_tiktok', '' ), 'icon' => 'svg-tiktok', 'title' => esc_html__( 'Follow us on Tiktok', 'quark' ) ),

			array( 'url' => of_get_option( 'social_tumblr', '' ), 'icon' => 'svg-tumblr', 'title' => esc_html__( 'Follow us on Tumblr', 'quark' ) ),
			array( 'url' => of_get_option( 'social_github', '' ), 'icon' => 'svg-substack', 'title' => esc_html__( 'Read us on Substack', 'quark' ) ),
			array( 'url' => of_get_option( 'social_bitbucket', '' ), 'icon' => 'svg-houzz', 'title' => esc_html__( 'Find us on Houzz', 'quark' ) ),
			array( 'url' => of_get_option( 'social_foursquare', '' ), 'icon' => 'svg-foursquare', 'title' => esc_html__( 'Follow us on Foursquare', 'quark' ) ),
			array( 'url' => of_get_option( 'social_youtube', '' ), 'icon' => 'svg-youtube', 'title' => esc_html__( 'Subscribe to us on YouTube', 'quark' ) ),
			array( 'url' => of_get_option( 'social_instagram', '' ), 'icon' => 'svg-instagram', 'title' => esc_html__( 'Follow us on Instagram', 'quark' ) ),
			array( 'url' => of_get_option( 'social_flickr', '' ), 'icon' => 'svg-flickr', 'title' => esc_html__( 'Connect with us on Flickr', 'quark' ) ),
			array( 'url' => of_get_option( 'social_pinterest', '' ), 'icon' => 'svg-pinterest', 'title' => esc_html__( 'Follow us on Pinterest', 'quark' ) ),
			array( 'url' => of_get_option( 'social_email', '' ), 'icon' => 'svg-envelope', 'title' => esc_html__( 'Contact Us', 'quark' ) ),
			array( 'url' => of_get_option( 'social_rss', '' ), 'icon' => 'svg-rss', 'title' => esc_html__( 'Subscribe to my RSS Feed', 'quark' ) )
		);

		foreach ( $icons as $key ) {
			$value = $key['url'];
			if ( !empty( $value ) ) {
				$output .= sprintf( '<li><a href="%1$s" class="sociallink" title="%2$s"%3$s><span class="icon %4$s"></span></a></li>',
					esc_url( $value ),
					$key['title'],
					( !of_get_option( 'social_newtab', '0' ) ? '' : ' target="_blank"' ),
					$key['icon']
                    

				);
			}
					
		}

		if ( !empty( $output ) ) {
			$output = '<ul>' . $output . '</ul>';
		}

		return $output;
	}
}
add_filter('wp_nav_menu_items','add_new_menu_item', 10, 2);
 //Adds home link before and socials after the menu
function add_new_menu_item( $nav, $args ) {
    if( $args->theme_location == 'primary' )
   $newmenuitem = "<li alt='link to home' class='home-link'> <a href='". esc_url( home_url( '/' ) ) ."' title='". esc_attr( get_bloginfo( 'name' ) )."' rel='home'><img src='".get_template_directory_uri() . "/images/FWicon-black.svg'/></a></li>";
   // $newmenuitem = "<li class='home-link'> <a href='". esc_url( home_url( '/' ) ) ."' title='". esc_attr( get_bloginfo( 'name' ) )."' rel='home'>home</a></li>";
    $nav = $newmenuitem.$nav;
    $nav .= '<li id="mobile-socials">';
        $nav.= do_shortcode("[socials]");
        $nav .= '</li>';
    return $nav;
}

