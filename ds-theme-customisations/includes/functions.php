<?php
/**
 * Functions.php
 *
 */

  //add page excerpts 
add_post_type_support( 'page', 'excerpt' );

// Better debug function - replace the previous one
function debug_video_meta() {
    global $post;
    if ($post && get_post_type($post->ID) === 'video') {
        echo '<div style="background: #fff; border: 2px solid #red; padding: 15px; margin: 10px 0;">';
        echo '<h3>🔍 Video Meta Debug (Post ID: ' . $post->ID . '):</h3>';
        echo '<pre style="background: #f5f5f5; padding: 10px; overflow: auto;">';
        
        $all_meta = get_post_meta($post->ID);
        $found_ovp_meta = false;
        
        foreach ($all_meta as $key => $value) {
            if (strpos($key, '_ovp_') === 0) {
                echo $key . ': ' . print_r($value[0], true) . "\n";
                $found_ovp_meta = true;
            }
        }
        
        if (!$found_ovp_meta) {
            echo "No _ovp_ meta fields found!\n";
        }
        
        echo '</pre></div>';
    }
}

// Try multiple hooks to make sure it shows up
add_action('edit_form_after_title', 'debug_video_meta');
add_action('add_meta_boxes', function() {
    add_meta_box('debug_meta', 'DEBUG: Video Meta Data', 'debug_video_meta', 'video', 'normal', 'high');
});