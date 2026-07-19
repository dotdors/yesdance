<?php
/**
 * Shared Template Parts — DS Location Manager
 *
 * Single source of truth for repeated front-end components. The grid
 * shortcode, news carousel, location archive, single-location template,
 * post location footer, and (child theme) news page all render through
 * these functions, so card markup/classes can never drift apart.
 *
 * All functions RETURN html (they don't echo), so callers can compose.
 */

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Two-color split heading (.ds-heading-split).
 *
 * @param string $heading Full heading text.
 * @param string|int $split Which word gets the accent color: 1-based word
 *                          number, or 'last' / -1 for the final word.
 * @param string $tag  Heading tag (h1/h2/h3...). Default h2.
 * @param string $extra_class Extra class(es) on the heading element.
 * @return string
 */
function ds_split_heading($heading, $split = 'last', $tag = 'h2', $extra_class = '') {
    $heading = trim((string) $heading);
    if ($heading === '') {
        return '';
    }

    $tag = preg_match('/^h[1-6]$/', $tag) ? $tag : 'h2';
    $parts = explode(' ', $heading);

    $class = trim('ds-heading-split ' . $extra_class);

    // Single-word titles never get the accent treatment — a lone accented
    // word reads as a mistake, not a design choice.
    if (count($parts) < 2) {
        return sprintf('<%1$s class="%2$s">%3$s</%1$s>', $tag, esc_attr($class), esc_html($heading));
    }

    if ($split === 'last' || $split === '-1' || intval($split) === -1) {
        $split = count($parts);
    } else {
        $split = intval($split);
    }

    // Out-of-range split: plain heading, no accent
    if ($split < 1 || $split > count($parts)) {
        return sprintf('<%1$s class="%2$s">%3$s</%1$s>', $tag, esc_attr($class), esc_html($heading));
    }

    $html = '<' . $tag . ' class="' . esc_attr($class) . '">';
    foreach ($parts as $index => $word) {
        $word_class = ($index + 1 === $split) ? 'ds-heading-split__accent' : 'ds-heading-split__primary';
        $html .= '<span class="' . esc_attr($word_class) . '">' . esc_html($word) . '</span>';
        if ($index < count($parts) - 1) {
            $html .= ' ';
        }
    }
    $html .= '</' . $tag . '>';

    return $html;
}

/**
 * Location card (.ds-location-card) — as used by the grid shortcode and
 * the locations archive. Styles: assets/location-grid.css.
 *
 * @param int $location_id ds_location post ID.
 * @return string
 */
function ds_render_location_card($location_id) {
    $name  = get_post_meta($location_id, '_ds_location_name', true) ?: get_the_title($location_id);
    $city  = get_the_title($location_id); // city IS the title (see DS_Location_Data)
    $image = get_the_post_thumbnail_url($location_id, 'large');
    $url   = get_permalink($location_id);

    ob_start();
    ?>
    <div class="ds-location-card">
        <a href="<?php echo esc_url($url); ?>" class="ds-location-card__link">

            <div class="ds-location-card__image">
                <?php if ($image) : ?>
                    <img src="<?php echo esc_url($image); ?>"
                         alt="<?php echo esc_attr($name); ?>"
                         loading="lazy">
                <?php else : ?>
                    <div class="ds-location-card__placeholder">
                        <span>📍</span>
                    </div>
                <?php endif; ?>

                <span class="ds-location-card__pin" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                        <circle cx="12" cy="10" r="3"></circle>
                    </svg>
                </span>
            </div>

            <div class="ds-location-card__content">
                <h3 class="ds-location-card__title"><?php echo esc_html($name); ?></h3>
                <?php if ($city) : ?>
                    <p class="ds-location-card__subtitle"><?php echo esc_html($city); ?></p>
                <?php endif; ?>
            </div>

        </a>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * News/post card (.ds-news-card) — as used by the news carousel and the
 * news page. Styles: assets/location-news-carousel.css (unscoped on
 * purpose so the card works outside the carousel).
 *
 * @param int   $post_id
 * @param array $args { show_badge?: bool (default true) }
 * @return string
 */
function ds_render_news_card($post_id, $args = array()) {
    $args = wp_parse_args($args, array(
        'show_badge' => true,
    ));

    $location = class_exists('DS_Location_News_Carousel')
        ? DS_Location_News_Carousel::get_post_location($post_id)
        : null;

    $featured_image = get_the_post_thumbnail_url($post_id, 'medium_large');
    $logo_url = ($location && !empty($location['logo_url'])) ? $location['logo_url'] : '';

    ob_start();
    ?>
    <article class="ds-news-card">
        <a href="<?php echo esc_url(get_permalink($post_id)); ?>" class="ds-news-card__link">

            <div class="ds-news-card__image">
                <?php if ($featured_image) : ?>
                    <img src="<?php echo esc_url($featured_image); ?>"
                         alt="<?php echo esc_attr(get_the_title($post_id)); ?>"
                         loading="lazy">
                <?php elseif ($logo_url) : ?>
                    <div class="ds-news-card__placeholder ds-news-card__placeholder--has-logo">
                        <img src="<?php echo esc_url($logo_url); ?>"
                             alt="<?php echo esc_attr($location['name']); ?>"
                             class="ds-news-card__placeholder-logo"
                             loading="lazy">
                    </div>
                <?php else : ?>
                    <div class="ds-news-card__placeholder"></div>
                <?php endif; ?>

                <?php if ($args['show_badge'] && $location) : ?>
                    <span class="ds-news-card__badge ds-news-card__badge--<?php echo esc_attr($location['type']); ?>">
                        <?php echo esc_html($location['name']); ?>
                    </span>
                <?php endif; ?>
            </div>

            <div class="ds-news-card__content">
                <h3 class="ds-news-card__title">
                    <?php echo esc_html(get_the_title($post_id)); ?>
                </h3>

                <time class="ds-news-card__date" datetime="<?php echo esc_attr(get_the_date('c', $post_id)); ?>">
                    <?php echo esc_html(get_the_date('', $post_id)); ?>
                </time>
            </div>

        </a>
    </article>
    <?php
    return ob_get_clean();
}

/**
 * Google Maps directions URL for a location — coordinates preferred,
 * name + address fallback, empty string when neither exists.
 *
 * @param array $data DS_Location_Data::get_all() array.
 * @return string
 */
function ds_location_directions_url(array $data) {
    if (!empty($data['latitude']) && !empty($data['longitude'])) {
        return 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode($data['latitude'] . ',' . $data['longitude']);
    }
    if (!empty($data['address'])) {
        $name = !empty($data['name']) ? $data['name'] : '';
        return 'https://www.google.com/maps/dir/?api=1&destination='
            . rawurlencode(trim($name . ' ' . $data['address']));
    }
    return '';
}

/**
 * Location contact card (.ds-contact-card) — as used by the single
 * location template's sidebar and the single-post location footer.
 * Styles: assets/location-template.css.
 *
 * @param int   $location_id
 * @param array $args {
 *   heading?:         string  Card heading. Default 'Contact Us'.
 *   show_directions?: bool    Default true.
 *   show_website?:    bool    Default true.
 *   extra_class?:     string  Extra class(es) on the aside.
 * }
 * @return string
 */
function ds_render_contact_card($location_id, $args = array()) {
    if (!class_exists('DS_Location_Data')) {
        return '';
    }

    $args = wp_parse_args($args, array(
        'heading'         => 'Contact Us',
        'show_directions' => true,
        'show_website'    => true,
        'extra_class'     => '',
    ));

    $data = DS_Location_Data::get_all($location_id);
    if (!$data) {
        return '';
    }

    $name = !empty($data['name']) ? $data['name'] : get_the_title($location_id);
    $directions_url = $args['show_directions'] ? ds_location_directions_url(array_merge($data, array('name' => $name))) : '';

    ob_start();
    ?>
    <aside class="ds-contact-card <?php echo esc_attr($args['extra_class']); ?>">
        <?php if ($args['heading']) : ?>
            <h2 class="ds-contact-card__heading"><?php echo esc_html($args['heading']); ?></h2>
        <?php endif; ?>

        <div class="ds-contact-card__group">
            <h3 class="ds-contact-card__label">Find Us</h3>
            <p class="ds-contact-card__name"><?php echo esc_html($name); ?></p>
            <?php if (!empty($data['address'])) : ?>
                <address class="ds-contact-card__address">
                    <?php echo nl2br(esc_html($data['address'])); ?>
                </address>
            <?php endif; ?>
        </div>

        <div class="ds-contact-card__group">
            <h3 class="ds-contact-card__label">Get In Touch</h3>
            <?php if (!empty($data['contact_name'])) : ?>
                <p class="ds-contact-card__item">Contact: <?php echo esc_html($data['contact_name']); ?></p>
            <?php endif; ?>
            <?php if (!empty($data['phone'])) : ?>
                <p class="ds-contact-card__item">
                    Call: <a href="tel:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $data['phone'])); ?>"><?php echo esc_html($data['phone']); ?></a>
                </p>
            <?php endif; ?>
            <?php if (!empty($data['text_phone'])) : ?>
                <p class="ds-contact-card__item">
                    Text: <a href="sms:<?php echo esc_attr(preg_replace('/[^0-9]/', '', $data['text_phone'])); ?>"><?php echo esc_html($data['text_phone']); ?></a>
                </p>
            <?php endif; ?>
            <?php if (!empty($data['email'])) : ?>
                <p class="ds-contact-card__item">
                    Email: <a href="mailto:<?php echo esc_attr($data['email']); ?>"><?php echo esc_html($data['email']); ?></a>
                </p>
            <?php endif; ?>
        </div>

        <?php if ($directions_url) : ?>
            <p class="ds-contact-card__directions">
                <a class="ds-btn ds-btn--solid" href="<?php echo esc_url($directions_url); ?>" target="_blank" rel="noopener noreferrer">
                    Get Directions
                </a>
            </p>
        <?php endif; ?>

        <?php if ($args['show_website'] && !empty($data['website'])) : ?>
            <p class="ds-contact-card__website">
                <a href="<?php echo esc_url($data['website']); ?>" target="_blank" rel="noopener noreferrer">
                    Visit our main website ↗
                </a>
            </p>
        <?php endif; ?>
    </aside>
    <?php
    return ob_get_clean();
}

/**
 * Resolve the ds_location post a regular post belongs to (via its
 * ds_post_location term → _ds_taxonomy_term_id mapping).
 *
 * @param int $post_id
 * @return int Location post ID, or 0 when the post has no location.
 */
function ds_get_post_location_id($post_id) {
    $terms = get_the_terms($post_id, 'ds_post_location');
    if (empty($terms) || is_wp_error($terms)) {
        return 0;
    }

    $location = get_posts(array(
        'post_type'   => 'ds_location',
        'meta_key'    => '_ds_taxonomy_term_id',
        'meta_value'  => $terms[0]->term_id,
        'post_status' => 'publish',
        'numberposts' => 1,
        'fields'      => 'ids',
    ));

    return !empty($location) ? (int) $location[0] : 0;
}
