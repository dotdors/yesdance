/**
     * Register Location Custom Post Type with Block Template
     */
    public function register_location_post_type() {
        $args = array(
            'label' => 'Locations',
            'labels' => array(
                'name' => 'Locations',
                'singular_name' => 'Location',
                'menu_name' => 'Locations',
                'add_new' => 'Add New Location',
                'add_new_item' => 'Add New Location',
                'edit_item' => 'Edit Location',
                'new_item' => 'New Location',
                'view_item' => 'View Location',
                'search_items' => 'Search Locations',
                'not_found' => 'No locations found',
                'not_found_in_trash' => 'No locations found in trash'
            ),
            'public' => true,
            'publicly_queryable' => true,
            'show_ui' => true,
            'show_in_menu' => true,
            'show_in_rest' => true,
            'rest_base' => 'locations',
            'query_var' => true,
            'rewrite' => array('slug' => 'location'),
            'capability_type' => array('location', 'locations'),
            'map_meta_cap' => true,
            'capabilities' => array(
                'edit_post'             => 'edit_location',
                'read_post'             => 'read_location',
                'delete_post'           => 'delete_location',
                'edit_posts'            => 'edit_locations',
                'edit_others_posts'     => 'edit_others_locations',
                'publish_posts'         => 'publish_locations',
                'read_private_posts'    => 'read_private_locations',
                'delete_posts'          => 'delete_locations',
                'delete_private_posts'  => 'delete_private_locations',
                'delete_published_posts'=> 'delete_published_locations',
                'delete_others_posts'   => 'delete_others_locations',
                'edit_private_posts'    => 'edit_private_locations',
                'edit_published_posts'  => 'edit_published_locations',
            ),
            'has_archive' => true,
            'hierarchical' => false,
            'menu_position' => 5,
            'menu_icon' => 'dashicons-location-alt',
            'supports' => array('title', 'editor', 'thumbnail', 'custom-fields', 'excerpt', 'author'),
            'template_lock' => false,
            // Default block template with shortcodes for dynamic content
            'template' => array(
                // Hero Section
                array('core/group', array(
                    'align' => 'full',
                    'className' => 'ds-location-hero-section',
                    'style' => array(
                        'spacing' => array(
                            'padding' => array(
                                'top' => '0',
                                'bottom' => '0',
                                'left' => '0',
                                'right' => '0'
                            )
                        )
                    )
                ), array(
                    array('core/shortcode', array(
                        'text' => '[ds_location_hero show_image="yes"]'
                    ))
                )),
                
                // Main Content Area
                array('core/group', array(
                    'style' => array(
                        'spacing' => array(
                            'padding' => array(
                                'top' => '3rem',
                                'bottom' => '3rem',
                                'left' => '1rem',
                                'right' => '1rem'
                            )
                        )
                    )
                ), array(
                    array('core/columns', array(), array(
                        // Main Content Column
                        array('core/column', array('width' => '66.66%'), array(
                            array('core/heading', array(
                                'level' => 2,
                                'content' => 'About This Location'
                            )),
                            array('core/paragraph', array(
                                'placeholder' => 'Add detailed information about this location, services offered, and what makes it special. This content will appear alongside the location details from your meta fields.'
                            )),
                            array('core/separator'),
                            array('core/heading', array(
                                'level' => 3,
                                'content' => 'Services & Features'
                            )),
                            array('core/paragraph', array(
                                'placeholder' => 'Describe the services, amenities, or features available at this location.'
                            )),
                            array('core/separator'),
                            array('core/heading', array(
                                'level' => 3,
                                'content' => 'Latest News & Updates'
                            )),
                            // Dynamic posts for this location
                            array('core/shortcode', array(
                                'text' => '[ds_location_posts posts_per_page="6" columns="2" show_images="yes"]'
                            ))
                        )),
                        
                        // Sidebar Column
                        array('core/column', array('width' => '33.33%'), array(
                            // Contact Information (dynamic)
                            array('core/shortcode', array(
                                'text' => '[ds_location_contact style="card"]'
                            )),
                            array('core/spacer', array('height' => '2rem')),
                            
                            // Hours/Additional Info
                            array('core/group', array(
                                'style' => array(
                                    'spacing' => array(
                                        'padding' => array(
                                            'top' => '1.5rem',
                                            'bottom' => '1.5rem',
                                            'left' => '1.5rem',
                                            'right' => '1.5rem'
                                        )
                                    ),
                                    'border' => array(
                                        'width' => '1px',
                                        'style' => 'solid',
                                        'color' => '#e0e0e0'
                                    )
                                ),
                                'backgroundColor' => 'white'
                            ), array(
                                array('core/heading', array(
                                    'level' => 4,
                                    'content' => 'Hours of Operation'
                                )),
                                array('core/paragraph', array(
                                    'placeholder' => 'Monday - Friday: 9:00 AM - 5:00 PM\nSaturday: 10:00 AM - 3:00 PM\nSunday: Closed'
                                ))
                            )),
                            
                            array('core/spacer', array('height' => '1rem')),
                            
                            // Quick Links
                            array('core/group', array(
                                'style' => array(
                                    'spacing' => array(
                                        'padding' => array(
                                            'top' => '1.5rem',
                                            'bottom' => '1.5rem',
                                            'left' => '1.5rem',
                                            'right' => '1.5rem'
                                        )
                                    ),
                                    'border' => array(
                                        'width' => '1px',
                                        'style' => 'solid',
                                        'color' => '#e0e0e0'
                                    )
                                ),
                                'backgroundColor' => 'white'
                            ), array(
                                array('core/heading', array(
                                    'level' => 4,
                                    'content' => 'Quick Links'
                                )),
                                array('core/list', array(), array(
                                    array('core/list-item', array('content' => '<a href="#services">Our Services</a>')),
                                    array('core/list-item', array('content' => '<a href="#directions">Directions</a>')),
                                    array('core/list-item', array('content' => '<a href="#parking">Parking Info</a>')),
                                    array('core/list-item', array('content' => '<a href="#contact">Contact Us</a>'))
                                ))
                            ))
                        ))
                    ))
                ))
            )
        );

        register_post_type('ds_location', $args);

        // Add custom fields hooks (meta boxes)
        add_action('add_meta_boxes', array($this, 'add_location_meta_boxes'));
        add_action('save_post', array($this, 'save_location_meta'));
    }