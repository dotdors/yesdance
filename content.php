<?php
/**
 * The default template for displaying content. Used for both single and index/archive/search.
 *
 * @package Quark
 * @since Quark 1.0
 */
?>

	<article id="post-<?php the_ID(); ?>" <?php post_class(); ?>>
		<?php if ( is_sticky() && is_home() && ! is_paged() ) { ?>
			<div class="featured-post">
				<?php esc_html_e( 'Featured post', 'quark' ); ?>
			</div>
		<?php } ?>
		<header class="entry-header">
			<?php if ( is_single() ) { ?>
				<h1 class="entry-title"><?php the_title(); ?></h1>
				
			<?php }
			else { ?>
			
				<h2 class="entry-title">
					<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( esc_html__( 'Link to %s', 'quark' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark"><?php the_title(); ?></a>
				</h2>
			<?php } // is_single() ?>
		
				
				
			
			
			<?php if ( has_post_thumbnail() && !is_search() ) { ?>
				<?php if ( is_single() ) { ?>
				<?php //the_post_thumbnail( 'thumbnail' ); ?>
				
			<?php }
			else { ?>
				<?php if (!is_category( gallery )) { ?>
				<a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( esc_html__( 'Link to %s', 'quark' ), the_title_attribute( 'echo=0' ) ) ); ?>">
					<?php the_post_thumbnail( 'thumbnail' ); ?>
				</a>
				
				<?php  } ?>
					<?php  } ?>
			<?php } ?>
		</header> <!-- /.entry-header -->
	
		<?php if ( 'tribe_events' == get_post_type() ) { ?>
<?php echo tribe_events_event_schedule_details( $event_id, '<h2 class="tribedate">', '</h2>' ); ?>
			<h2 class="venueinfo"><?php echo tribe_get_venue(); ?>
			<?php if (tribe_get_venue()){echo ',';} ?> <?php echo tribe_get_city(); ?> <?php echo tribe_get_state(); ?>
			</h2>
			<a class="eventlink" href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( esc_html__( 'Link to %s', 'quark' ), the_title_attribute( 'echo=0' ) ) ); ?>">[event details]</a><P>
	<?php } ?>
		<?php if ( is_search() || is_archive() ) { // Only display Excerpts for Search ?>
			<div class="entry-summary">

				<?php if (is_category( 'gallery' )) {
				// the_content();	this option shows full post on gallery posts.
				//the_excerpt();
				//echo 'test';
				

	$args = array(
		'numberposts' => 6,
		'order' => 'ASC',
		'post_mime_type' => 'image',
		'post_parent' => $post->ID,
		'post_status' => null,
		'post_type' => 'attachment',
	);

	$attachments = get_children( $args );

	if ( $attachments ) {
		foreach ( $attachments as $attachment ) {
			$image_attributes = wp_get_attachment_image_src( $attachment->ID, 'thumbnail' )  ? wp_get_attachment_image_src( $attachment->ID, 'thumbnail' ) : wp_get_attachment_image_src( $attachment->ID, 'full' );

			echo '<img src="' . wp_get_attachment_thumb_url( $attachment->ID ) . '" class="current">';
		}
	}
?>
				<div class='moregallery'><a href="<?php the_permalink(); ?>" title="<?php echo esc_attr( sprintf( esc_html__( 'Link to %s', 'quark' ), the_title_attribute( 'echo=0' ) ) ); ?>" rel="bookmark">View more photos from <?php the_title(); ?><br/>&rarr;</a>	
				<?php //<div class='arrow-right'></div> ?>
				</div>	
				
				<?php
				} else {
the_excerpt();
				}
?>
			</div> <!-- /.entry-summary -->
		<?php }
		else { ?>
			<div class="entry-content">
			<?php if ( is_singular() ) {
				//  Single Post page
				the_content(); 
			} else {?>
				<?php the_excerpt( wp_kses( __( 'Continue reading <span class="meta-nav">&rarr;</span>', 'quark' ), array( 'span' => array( 'class' => array() ) ) )	); ?>
				<?php wp_link_pages( array(
					'before' => '<div class="page-links">' . esc_html__( 'Pages:', 'quark' ),
					'after' => '</div>',
					'link_before' => '<span class="page-numbers">',
					'link_after' => '</span>'
				) ); ?>
			</div> <!-- /.entry-content -->
			<?php } ?>
		<?php } ?>

		<footer class="entry-meta">
			<?php if ( is_singular() ) {
				// Only show the tags on the Single Post page
				quark_entry_meta();
			} ?>
			<?php edit_post_link( esc_html__( 'Edit', 'quark' ) . ' <i class="fa fa-angle-right"></i>', '<div class="edit-link">', '</div>' ); ?>
			<?php if ( is_singular() && get_the_author_meta( 'description' ) && is_multi_author() ) {
				// If a user has filled out their description and this is a multi-author blog, show their bio
				get_template_part( 'author-bio' );
			} ?>
		</footer> <!-- /.entry-meta -->
	</article> <!-- /#post -->
