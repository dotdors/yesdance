<?php
/**
 * The Template for displaying all single posts.
 *
 * @package Quark
 * @since Quark 1.0
 */

get_header(); ?>

	

<div id="primary" class="site-size site-content row" role="main">
		<div class="col grid_12_of_12">

			<?php if ( have_posts() ) : ?>

				<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', 'page' ); ?>
					
				<?php endwhile; // end of the loop. ?>

			<?php endif; // end have_posts() check ?>

		</div> <!-- /.col.grid_12_of_12 -->
	</div><!-- /#primary.site-content.row -->

	

<?php get_footer(); ?>
