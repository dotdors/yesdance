<?php
// Minimal template part for listing content excerpts
?>
<article id="post-<?php the_ID(); ?>" <?php post_class(); ?> >
  <h2><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h2>
  <div class="entry-summary"><?php the_excerpt(); ?></div>
</article>
