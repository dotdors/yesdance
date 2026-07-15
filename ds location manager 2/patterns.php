<?php
/**
 * Block patterns for DS Location Manager
 * 
 * Patterns are available in the block inserter but NOT auto-inserted.
 * Location display is handled by single-ds_location.php template.
 */

add_action('init', function() {
    if (!function_exists('register_block_pattern_category')) {
        return; // Gutenberg not active
    }

    // Register category
    register_block_pattern_category(
        'ds-location',
        array('label' => __('Location Templates', 'ds-location'))
    );

    // Enhanced Location Landing Page Pattern
    register_block_pattern(
        'ds-location/complete-landing-page',
        array(
            'title'       => __('Complete Location Landing Page', 'ds-location'),
            'description' => __('Full location page template with hero section, contact info, description, and recent posts.', 'ds-location'),
            'categories'  => array('ds-location'),
            'content'     => '
<!-- wp:group {"align":"full","style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1rem","right":"1rem"}}},"backgroundColor":"light-gray","className":"ds-location-hero"} -->
<div class="wp-block-group alignfull ds-location-hero has-light-gray-background-color has-background" style="padding-top:2rem;padding-right:1rem;padding-bottom:2rem;padding-left:1rem">
    <!-- wp:columns {"verticalAlignment":"center"} -->
    <div class="wp-block-columns are-vertically-aligned-center">
        <!-- wp:column {"verticalAlignment":"center","width":"66.66%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:66.66%">
            <!-- wp:heading {"level":1,"style":{"typography":{"fontSize":"3rem","fontWeight":"700"}}} -->
            <h1 style="font-size:3rem;font-weight:700">[Location Name]</h1>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph {"style":{"typography":{"fontSize":"1.2rem"}}} -->
            <p style="font-size:1.2rem">[Location Description]</p>
            <!-- /wp:paragraph -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"verticalAlignment":"center","width":"33.33%"} -->
        <div class="wp-block-column is-vertically-aligned-center" style="flex-basis:33.33%">
            <!-- wp:image {"sizeSlug":"large","linkDestination":"none","className":"ds-location-featured-image"} -->
            <figure class="wp-block-image size-large ds-location-featured-image">
                <img alt="Location Photo"/>
            </figure>
            <!-- /wp:image -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->

<!-- wp:group {"style":{"spacing":{"padding":{"top":"2rem","bottom":"2rem","left":"1rem","right":"1rem"}}}} -->
<div class="wp-block-group" style="padding-top:2rem;padding-right:1rem;padding-bottom:2rem;padding-left:1rem">
    <!-- wp:columns -->
    <div class="wp-block-columns">
        <!-- wp:column {"width":"66.66%"} -->
        <div class="wp-block-column" style="flex-basis:66.66%">
            <!-- wp:heading {"level":2} -->
            <h2>About This Location</h2>
            <!-- /wp:heading -->
            
            <!-- wp:paragraph -->
            <p>[Location Full Description - Replace with detailed information about this location]</p>
            <!-- /wp:paragraph -->
            
            <!-- wp:separator -->
            <hr class="wp-block-separator"/>
            <!-- /wp:separator -->
            
            <!-- wp:heading {"level":3} -->
            <h3>Latest Updates &amp; News</h3>
            <!-- /wp:heading -->
            
            <!-- wp:query {"queryId":1,"query":{"perPage":6,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list","columns":2}} -->
            <div class="wp-block-query">
                <!-- wp:post-template -->
                <!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem","left":"1rem","right":"1rem"},"margin":{"bottom":"1.5rem"}},"border":{"width":"1px","style":"solid","color":"#e0e0e0"}},"backgroundColor":"white"} -->
                <div class="wp-block-group has-white-background-color has-background" style="border-color:#e0e0e0;border-style:solid;border-width:1px;margin-bottom:1.5rem;padding-top:1rem;padding-right:1rem;padding-bottom:1rem;padding-left:1rem">
                    <!-- wp:post-featured-image {"isLink":true,"height":"200px"} /-->
                    
                    <!-- wp:post-title {"level":4,"isLink":true} /-->
                    
                    <!-- wp:post-excerpt {"moreText":"Read More","showMoreOnNewLine":false} /-->
                    
                    <!-- wp:post-date {"style":{"typography":{"fontSize":"0.9rem"}},"textColor":"medium-gray"} /-->
                </div>
                <!-- /wp:group -->
                <!-- /wp:post-template -->
                
                <!-- wp:query-pagination -->
                <!-- wp:query-pagination-previous /-->
                <!-- wp:query-pagination-numbers /-->
                <!-- wp:query-pagination-next /-->
                <!-- /wp:query-pagination -->
            </div>
            <!-- /wp:query -->
        </div>
        <!-- /wp:column -->
        
        <!-- wp:column {"width":"33.33%"} -->
        <div class="wp-block-column" style="flex-basis:33.33%">
            <!-- wp:group {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}},"border":{"width":"1px","style":"solid","color":"#e0e0e0"}},"backgroundColor":"light-gray","className":"ds-location-contact"} -->
            <div class="wp-block-group ds-location-contact has-light-gray-background-color has-background" style="border-color:#e0e0e0;border-style:solid;border-width:1px;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem">
                <!-- wp:heading {"level":4} -->
                <h4>Contact Information</h4>
                <!-- /wp:heading -->
                
                <!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
                <p style="font-weight:600">[Contact Name]</p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph -->
                <p><strong>Address:</strong><br>[Location Address]</p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph -->
                <p><strong>Phone:</strong><br>[Location Phone]</p>
                <!-- /wp:paragraph -->
                
                <!-- wp:paragraph -->
                <p><strong>Email:</strong><br><a href="mailto:[Location Email]">[Location Email]</a></p>
                <!-- /wp:paragraph -->
            </div>
            <!-- /wp:group -->
            
            <!-- wp:spacer {"height":"2rem"} -->
            <div style="height:2rem" aria-hidden="true" class="wp-block-spacer"></div>
            <!-- /wp:spacer -->
            
            <!-- wp:group {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}},"border":{"width":"1px","style":"solid","color":"#e0e0e0"}},"backgroundColor":"white"} -->
            <div class="wp-block-group has-white-background-color has-background" style="border-color:#e0e0e0;border-style:solid;border-width:1px;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem">
                <!-- wp:heading {"level":4} -->
                <h4>Quick Links</h4>
                <!-- /wp:heading -->
                
                <!-- wp:list -->
                <ul>
                    <li><a href="#services">Our Services</a></li>
                    <li><a href="#hours">Hours of Operation</a></li>
                    <li><a href="#directions">Directions</a></li>
                    <li><a href="#contact">Contact Us</a></li>
                </ul>
                <!-- /wp:list -->
            </div>
            <!-- /wp:group -->
        </div>
        <!-- /wp:column -->
    </div>
    <!-- /wp:columns -->
</div>
<!-- /wp:group -->
            ',
        )
    );

    // Simplified Contact Info Pattern
    register_block_pattern(
        'ds-location/contact-info',
        array(
            'title'       => __('Location Contact Info', 'ds-location'),
            'description' => __('Contact information block for location pages.', 'ds-location'),
            'categories'  => array('ds-location'),
            'content'     => '
<!-- wp:group {"style":{"spacing":{"padding":{"top":"1.5rem","bottom":"1.5rem","left":"1.5rem","right":"1.5rem"}},"border":{"width":"1px","style":"solid","color":"#e0e0e0"}},"backgroundColor":"light-gray","className":"ds-location-contact"} -->
<div class="wp-block-group ds-location-contact has-light-gray-background-color has-background" style="border-color:#e0e0e0;border-style:solid;border-width:1px;padding-top:1.5rem;padding-right:1.5rem;padding-bottom:1.5rem;padding-left:1.5rem">
    <!-- wp:heading {"level":3} -->
    <h3>Contact Information</h3>
    <!-- /wp:heading -->
    
    <!-- wp:paragraph {"style":{"typography":{"fontWeight":"600"}}} -->
    <p style="font-weight:600">[Contact Name]</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p><strong>Address:</strong><br>[Location Address]</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p><strong>Phone:</strong><br>[Location Phone]</p>
    <!-- /wp:paragraph -->
    
    <!-- wp:paragraph -->
    <p><strong>Email:</strong><br><a href="mailto:[Location Email]">[Location Email]</a></p>
    <!-- /wp:paragraph -->
</div>
<!-- /wp:group -->
            ',
        )
    );

    // Recent Posts Pattern
    register_block_pattern(
        'ds-location/recent-posts',
        array(
            'title'       => __('Location Recent Posts', 'ds-location'),
            'description' => __('Display recent posts for this location.', 'ds-location'),
            'categories'  => array('ds-location'),
            'content'     => '
<!-- wp:heading {"level":3} -->
<h3>Latest Updates</h3>
<!-- /wp:heading -->

<!-- wp:query {"queryId":2,"query":{"perPage":4,"pages":0,"offset":0,"postType":"post","order":"desc","orderBy":"date","author":"","search":"","exclude":[],"sticky":"","inherit":false},"displayLayout":{"type":"list"}} -->
<div class="wp-block-query">
    <!-- wp:post-template -->
    <!-- wp:group {"style":{"spacing":{"padding":{"top":"1rem","bottom":"1rem","left":"1rem","right":"1rem"},"margin":{"bottom":"1.5rem"}},"border":{"width":"1px","style":"solid","color":"#e0e0e0"}},"backgroundColor":"white"} -->
    <div class="wp-block-group has-white-background-color has-background" style="border-color:#e0e0e0;border-style:solid;border-width:1px;margin-bottom:1.5rem;padding-top:1rem;padding-right:1rem;padding-bottom:1rem;padding-left:1rem">
        <!-- wp:post-featured-image {"isLink":true,"height":"150px"} /-->
        
        <!-- wp:post-title {"level":4,"isLink":true} /-->
        
        <!-- wp:post-excerpt {"moreText":"Read More","showMoreOnNewLine":false} /-->
        
        <!-- wp:group {"style":{"spacing":{"margin":{"top":"0.5rem"}}},"layout":{"type":"flex","justifyContent":"space-between"}} -->
        <div class="wp-block-group" style="margin-top:0.5rem">
            <!-- wp:post-date {"style":{"typography":{"fontSize":"0.9rem"}},"textColor":"medium-gray"} /-->
            <!-- wp:post-author {"showAvatar":false,"style":{"typography":{"fontSize":"0.9rem"}},"textColor":"medium-gray"} /-->
        </div>
        <!-- /wp:group -->
    </div>
    <!-- /wp:group -->
    <!-- /wp:post-template -->
    
    <!-- wp:query-pagination -->
    <!-- wp:query-pagination-previous /-->
    <!-- wp:query-pagination-numbers /-->
    <!-- wp:query-pagination-next /-->
    <!-- /wp:query-pagination -->
</div>
<!-- /wp:query -->
            ',
        )
    );
});
