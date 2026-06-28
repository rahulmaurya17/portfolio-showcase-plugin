<?php
/*
Plugin Name: Portfolio Showcase
Description: A custom portfolio showcase plugin to manage and display projects using shortcode.
Version: 1.0.0
Author: Rahul Maurya
*/

if (!defined('ABSPATH')) {
    exit;
}

// Register Custom Post Type
function ps_register_portfolio_post_type() {
    $args = array(
        'labels' => array(
            'name' => 'Portfolio Projects',
            'singular_name' => 'Portfolio Project',
            'add_new' => 'Add New Project',
            'add_new_item' => 'Add New Portfolio Project',
            'edit_item' => 'Edit Portfolio Project',
            'new_item' => 'New Portfolio Project',
            'view_item' => 'View Portfolio Project',
            'search_items' => 'Search Portfolio Projects',
            'not_found' => 'No portfolio projects found',
            'menu_name' => 'Portfolio Projects',
        ),
        'public' => true,
        'menu_icon' => 'dashicons-portfolio',
        'supports' => array('title', 'editor', 'thumbnail'),
        'has_archive' => true,
        'rewrite' => array('slug' => 'portfolio-projects'),
        'show_in_rest' => true,
    );

    register_post_type('portfolio_project', $args);
}
add_action('init', 'ps_register_portfolio_post_type');

// Enqueue Plugin Styles 
function ps_enqueue_styles() {
    wp_enqueue_style(
        'ps-portfolio-style',
        plugin_dir_url(__FILE__) . 'assets/css/style.css',
        array(),
        time()
    );

    wp_enqueue_script(
        'ps-portfolio-script',
        plugin_dir_url(__FILE__) . 'assets/js/script.js',
        array(),
        time(),
        true
    );
}
add_action('wp_enqueue_scripts', 'ps_enqueue_styles');

// Portfolio Shortcode
function ps_portfolio_showcase_shortcode() {
    $args = array(
        'post_type' => 'portfolio_project',
        'posts_per_page' => -1,
        'post_status' => 'publish',
    );

    $query = new WP_Query($args);
    ob_start();

    if ($query->have_posts()) {
        $categories = array();

        while ($query->have_posts()) {
            $query->the_post();
            $cat = get_post_meta(get_the_ID(), '_ps_project_category', true);

            if (!empty($cat)) {
                $categories[] = $cat;
            }
        }

        $categories = array_unique($categories);

        echo '<div class="ps-filter-tabs">';
        echo '<button class="ps-filter-btn active" data-filter="all">All</button>';

        foreach ($categories as $category) {
            echo '<button class="ps-filter-btn" data-filter="' . esc_attr(sanitize_title($category)) . '">' . esc_html($category) . '</button>';
        }

        echo '</div>';

        echo '<div class="ps-portfolio-grid">';

        $query->rewind_posts();

        while ($query->have_posts()) {
            $query->the_post();

            $project_id = get_the_ID();

            $category = get_post_meta($project_id, '_ps_project_category', true);
            $status = get_post_meta($project_id, '_ps_project_status', true);
            $technologies = get_post_meta($project_id, '_ps_project_technologies', true);
            $live_url = get_post_meta($project_id, '_ps_live_url', true);
            $github_url = get_post_meta($project_id, '_ps_github_url', true);
            $client_name = get_post_meta($project_id, '_ps_client_name', true);
            $project_year = get_post_meta($project_id, '_ps_project_year', true);

            echo '<div class="ps-portfolio-card" data-category="' . esc_attr(sanitize_title($category)) . '">';

            if (has_post_thumbnail()) {
                echo '<div class="ps-portfolio-image">';
                the_post_thumbnail('medium');
                echo '</div>';
            }

            echo '<div class="ps-portfolio-content">';
            echo '<h3>' . esc_html(get_the_title()) . '</h3>';
            echo '<p>' . esc_html(wp_trim_words(get_the_content(), 20)) . '</p>';

            echo '<div class="ps-project-meta">';

            if (!empty($category)) {
                echo '<p><strong>Category:</strong> ' . esc_html($category) . '</p>';
            }

            if (!empty($status)) {
                echo '<p><strong>Status:</strong> ' . esc_html($status) . '</p>';
            }

            if (!empty($technologies)) {
                echo '<p><strong>Technologies:</strong> ' . esc_html($technologies) . '</p>';
            }

            if (!empty($client_name)) {
                echo '<p><strong>Client:</strong> ' . esc_html($client_name) . '</p>';
            }

            if (!empty($project_year)) {
                echo '<p><strong>Year:</strong> ' . esc_html($project_year) . '</p>';
            }

            echo '</div>';

            echo '<div class="ps-project-buttons">';

            if (!empty($live_url)) {
                echo '<a href="' . esc_url($live_url) . '" target="_blank">Live Demo</a>';
            }

            if (!empty($github_url)) {
                echo '<a href="' . esc_url($github_url) . '" target="_blank">GitHub</a>';
            }

            echo '</div>';
            echo '</div>';
            echo '</div>';
        }

        echo '</div>';
    } else {
        echo '<p>No portfolio projects found.</p>';
    }

    wp_reset_postdata();

    return ob_get_clean();
}
add_shortcode('portfolio_showcase', 'ps_portfolio_showcase_shortcode');

// Add Project Details Meta Box
function ps_add_project_details_meta_box() {
    add_meta_box(
        'ps_project_details',
        'Project Details',
        'ps_project_details_callback',
        'portfolio_project',
        'normal',
        'high'
    );
}
add_action('add_meta_boxes', 'ps_add_project_details_meta_box');


// Meta Box HTML Fields
function ps_project_details_callback($post) {
    $category = get_post_meta($post->ID, '_ps_project_category', true);
    $status = get_post_meta($post->ID, '_ps_project_status', true);
    $technologies = get_post_meta($post->ID, '_ps_project_technologies', true);
    $live_url = get_post_meta($post->ID, '_ps_live_url', true);
    $github_url = get_post_meta($post->ID, '_ps_github_url', true);
    $client_name = get_post_meta($post->ID, '_ps_client_name', true);
    $project_year = get_post_meta($post->ID, '_ps_project_year', true);

    wp_nonce_field('ps_save_project_details', 'ps_project_details_nonce');
    ?>

    <p>
<select name="ps_project_category" style="width:100%;">
    <option value="">Select Category</option>
    <option value="Plugin" <?php selected($category, 'Plugin'); ?>>Plugin</option>
    <option value="WordPress Website" <?php selected($category, 'WordPress Website'); ?>>WordPress Website</option>
    <option value="Core PHP" <?php selected($category, 'Core PHP'); ?>>Core PHP</option>
    <option value="Shopify" <?php selected($category, 'Shopify'); ?>>Shopify</option>
    <option value="Wix" <?php selected($category, 'Wix'); ?>>Wix</option>
</select>
    </p>

    <p>
        <label><strong>Project Status</strong></label><br>
        <select name="ps_project_status" style="width:100%;">
            <option value="">Select Status</option>
            <option value="Completed" <?php selected($status, 'Completed'); ?>>Completed</option>
            <option value="In Progress" <?php selected($status, 'In Progress'); ?>>In Progress</option>
            <option value="Maintenance" <?php selected($status, 'Maintenance'); ?>>Maintenance</option>
        </select>
    </p>

    <p>
        <label><strong>Technologies Used</strong></label><br>
        <input type="text" name="ps_project_technologies" value="<?php echo esc_attr($technologies); ?>" style="width:100%;" placeholder="WordPress, PHP, CSS, JavaScript">
    </p>

    <p>
        <label><strong>Live Demo URL</strong></label><br>
        <input type="url" name="ps_live_url" value="<?php echo esc_url($live_url); ?>" style="width:100%;">
    </p>

    <p>
        <label><strong>GitHub / Code URL</strong></label><br>
        <input type="url" name="ps_github_url" value="<?php echo esc_url($github_url); ?>" style="width:100%;">
    </p>

    <p>
        <label><strong>Client / Company Name</strong></label><br>
        <input type="text" name="ps_client_name" value="<?php echo esc_attr($client_name); ?>" style="width:100%;">
    </p>

    <p>
        <label><strong>Project Year</strong></label><br>
        <input type="number" name="ps_project_year" value="<?php echo esc_attr($project_year); ?>" style="width:100%;" placeholder="2026">
    </p>

    <?php
}


// Save Meta Box Data
function ps_save_project_details($post_id) {
    if (!isset($_POST['ps_project_details_nonce'])) {
        return;
    }

    if (!wp_verify_nonce($_POST['ps_project_details_nonce'], 'ps_save_project_details')) {
        return;
    }

    if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }

    if (!current_user_can('edit_post', $post_id)) {
        return;
    }

    update_post_meta($post_id, '_ps_project_category', sanitize_text_field($_POST['ps_project_category']));
    update_post_meta($post_id, '_ps_project_status', sanitize_text_field($_POST['ps_project_status']));
    update_post_meta($post_id, '_ps_project_technologies', sanitize_text_field($_POST['ps_project_technologies']));
    update_post_meta($post_id, '_ps_live_url', esc_url_raw($_POST['ps_live_url']));
    update_post_meta($post_id, '_ps_github_url', esc_url_raw($_POST['ps_github_url']));
    update_post_meta($post_id, '_ps_client_name', sanitize_text_field($_POST['ps_client_name']));
    update_post_meta($post_id, '_ps_project_year', sanitize_text_field($_POST['ps_project_year']));
}
add_action('save_post', 'ps_save_project_details');