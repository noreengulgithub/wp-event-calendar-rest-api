<?php
/*
Plugin Name: Events List via REST
Description: Display events in a list via shortcode using REST API.
Version: 1.0
Author: Noreen
*/

if (!defined('ABSPATH')) exit;

// 1. Register Custom Post Type
function el_register_events_cpt() {
    $labels = [
        'name' => 'Events',
        'singular_name' => 'Event',
        'menu_name' => 'Events',
    ];
    $args = [
        'labels' => $labels,
        'public' => true,
        'has_archive' => true,
        'supports' => ['title', 'editor', 'thumbnail'],
        'show_in_rest' => true,
    ];
    register_post_type('el_event', $args);
}
add_action('init', 'el_register_events_cpt');

// 2. Add Event Date Meta Box
function el_event_date_meta_box() {
    add_meta_box('el_event_date', 'Event Date', 'el_event_date_callback', 'el_event', 'side', 'default');
}
add_action('add_meta_boxes', 'el_event_date_meta_box');

function el_event_date_callback($post) {
    wp_nonce_field('el_save_event_date', 'el_event_date_nonce');
    $value = get_post_meta($post->ID, '_el_event_date', true);
    echo '<input type="date" name="el_event_date" value="'.esc_attr($value).'" style="width:100%;">';
}

function el_save_event_date($post_id) {
    if (!isset($_POST['el_event_date_nonce']) || !wp_verify_nonce($_POST['el_event_date_nonce'], 'el_save_event_date')) return;
    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) return;
    if (isset($_POST['el_event_date'])) {
        update_post_meta($post_id, '_el_event_date', sanitize_text_field($_POST['el_event_date']));
    }
}
add_action('save_post', 'el_save_event_date');

// 3. REST API Endpoint
add_action('rest_api_init', function() {
    register_rest_route('el/v1', '/events', [
        'methods' => 'GET',
        'callback' => 'el_get_events',
    ]);
});

function el_get_events($request) {
    $args = [
        'post_type' => 'el_event',
        'post_status' => 'publish',
        'orderby' => 'meta_value',
        'meta_key' => '_el_event_date',
        'order' => 'ASC',
        'posts_per_page' => -1
    ];

    $query = new WP_Query($args);
    $events = [];

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();
            $events[] = [
                'id' => get_the_ID(),
                'title' => get_the_title(),
                'description' => get_the_content(),
                'photo' => get_the_post_thumbnail_url(get_the_ID(), 'medium'),
                'date' => get_post_meta(get_the_ID(), '_el_event_date', true),
                'link' => get_permalink(),
            ];
        }
        wp_reset_postdata();
    }

    return $events;
}

// 4. Enqueue Scripts & Styles
function el_enqueue_scripts() {
    wp_enqueue_style('el-style', plugin_dir_url(__FILE__) . 'assets/el-style.css');
    wp_enqueue_script('el-script', plugin_dir_url(__FILE__) . 'assets/el-script.js', ['jquery'], null, true);
    wp_localize_script('el-script', 'el_ajax_obj', [
        'rest_url' => esc_url(rest_url('el/v1/events'))
    ]);
}
add_action('wp_enqueue_scripts', 'el_enqueue_scripts');

// 5. Shortcode
function el_events_list_shortcode() {
    return '<div id="el-events-list">Loading events...</div>';
}
add_shortcode('events_list', 'el_events_list_shortcode');

function el_event_admin_notice() {
    $screen = get_current_screen();
    if ($screen->post_type === 'el_event' || $screen->id === 'page' || $screen->id === 'post') {
        echo '<div class="notice notice-info is-dismissible">
            <p><strong>Event Shortcodes:</strong></p>
            <p>➡️ Use <code>[event_list]</code> to show all events.</p> 
        </div>';
    }
}
add_action('admin_notices', 'el_event_admin_notice');
