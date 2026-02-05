<?php

/*
* Template Name: page-brand-archive
*/

get_header();

// Extract brand slug from the current URL.
$uri_segments = explode('/', trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/'));
$brand_slug = end($uri_segments); // Assuming the brand slug is the last segment.

// Convert slug to ID (assuming your function to get brand ID by slug is correct).
$brand_id = get_brand_id_by_slug($brand_slug);

// Query for related 'test' posts.
$args = array(
    'post_type' => 'test',
    'posts_per_page' => -1,
    'meta_query' => array(
        array(
            'key' => 'marque', // Your ACF field name.
            'value' => '"' . $brand_id . '"',
            'compare' => 'LIKE'
        )
    )
);

$related_tests = new WP_Query($args);

if ($related_tests->have_posts()) {
    echo '<ul>';
    while ($related_tests->have_posts()) {
        $related_tests->the_post();
        echo '<li><a href="' . get_permalink() . '">' . get_the_title() . '</a></li>';
    }
    echo '</ul>';
} else {
    echo 'No related tests found for this brand.';
}

wp_reset_postdata();
get_footer();
