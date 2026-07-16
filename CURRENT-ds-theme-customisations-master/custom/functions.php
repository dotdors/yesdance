<?php
/**
 * Functions.php
 *
 */

  //add page excerpts 
add_post_type_support( 'page', 'excerpt' );

add_action('epc_purge_request', '__return_null');