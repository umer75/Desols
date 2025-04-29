<?php

if (!defined('ABSPATH')) {
    exit;
}

/**
 * Class for handling script enqueuing in the WordPress admin area
 */
class Desol_Enqueue
{
    public function __construct()
    {
        add_action('admin_enqueue_scripts', [$this, 'enqueue_admin_scripts']);
        add_action('wp_ajax_fetch_average_salary', [$this, 'fetch_average_salary']); // Handle the AJAX request
    }

    public static function init() {
        // Initialize hooks or setup code
        new self(); // Instantiate the class
    }

    /**
     * Enqueue admin scripts for custom admin page
     */
    public function enqueue_admin_scripts($hook)
    {
        // Only load on the employee report page
        if ($hook !== 'employee_page_employee-report') {
        //     return;
        }

        // Enqueue the custom JavaScript for the admin page
        wp_enqueue_script(
            'desol-admin-scripts', // Handle
            DESOL_PLUGIN_URL . 'assets/js/desol-admin-scripts.js', // File path to the JS file
            array('jquery'), // Dependencies (jQuery)
            null,
            true // Load script in footer
        );

        // Pass the Ajax URL to the script
        // Generate a nonce for AJAX security
        $nonce = wp_create_nonce('fetch_average_salary_nonce');

        // Pass the Ajax URL and nonce to the script
        wp_localize_script(
            'desol-admin-scripts',
            'desol_nonce_data', // The object to store nonce data
            array(
                'ajaxurl' => admin_url('admin-ajax.php'),
                'desol_nonce' => $nonce
            )
        );
    }

    /**
     * Handle the AJAX request to calculate the average salary
     */
    public function fetch_average_salary()
    {

        // Verify the nonce
        if (!isset($_POST['desol_nonce']) || !wp_verify_nonce($_POST['desol_nonce'], 'fetch_average_salary_nonce')) {
            wp_send_json_error(['message' => 'Nonce verification failed']);
            return;
        }

        global $wpdb;

        // Query the database to fetch employee salaries
        $results = $wpdb->get_results("SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = '_employee_salary'");

        if (empty($results)) {
            wp_send_json_error(); // No salaries found
        }

        // Calculate the average salary
        $total_salary = 0;
        $count = 0;

        foreach ($results as $result) {
            $total_salary += (float) $result->meta_value;
            $count++;
        }

        $average_salary = $count > 0 ? $total_salary / $count : 0;

        // Return the average salary as a response
        wp_send_json_success(['average_salary' => number_format($average_salary, 2)]);
    }
}
