<?php
/*
Plugin Name: Kapitalwise Smart Widget
Description: Kapitalwise Smart Widget Wordpress Plugin
Version: 1.0.0
Requires at least: 4.7
Requires PHP: 7.0
Author: Kapitalwise.com
Author URI: https://kapitalwise.com
License: GPL v2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if(!defined('ABSPATH')) 
    exit; 

// Define global variables for API URL
define('KWSW_API_URL', 'https://ncapi.kapitalwise.com/wordpress/smartWidget');

// Add settings page
function kwsw_settings_page() {
    add_options_page('Kapitalwise Smart Widget Settings', 'Kapitalwise Smart Widget', 'manage_options', 'kapitalwise_smart_widget', 'kwsw_settings_page_content');
}
add_action('admin_menu', 'kwsw_settings_page');

// Register settings
function kwsw_register_settings() {
    register_setting('kwsw-settings-group', 'kwsw_api_key');
}
add_action('admin_init', 'kwsw_register_settings');

// Settings page content
function kwsw_settings_page_content() {
    ?>
    <div class="wrap">
        <h2>Kapitalwise Smart Widget Settings</h2>
        <form method="post" action="options.php">
            <?php settings_fields('kwsw-settings-group'); ?>
            <?php do_settings_sections('kwsw-settings-group'); ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key:</th>
                    <td><input type="text" name="kwsw_api_key" value="<?php echo esc_attr(get_option('kwsw_api_key')); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Shortcode callback function
function kwsw_shortcode_callback($atts) {
    
    // Retrieve API key from settings
    $api_key = get_option('kwsw_api_key');
    
    // Return if api key is not present 
    if (empty($api_key)) {
        return '<p style="text-align:center"> Please enter a valid API key in settings </p>';
    }

    // Make API call using $api_key as Bearer token in Authorization header
    $api_response = wp_remote_get(KWSW_API_URL, array(
        'headers' => array(
            'Authorization' => 'Bearer ' . $api_key,
        ),
    ));

    // Process API response
    if (!is_wp_error($api_response) && wp_remote_retrieve_response_code($api_response) === 200) {
        $body = wp_remote_retrieve_body($api_response);
        $data = json_decode($body, true);
        
        // Return kapitalwise widget script
        return $data;
    } else {
        // Return empty string to prevent shortcode output on the website
        return '<p style="text-align:center"> You haven’t configured any SmartWidget, please contact Kapitalwise at support@kapitalwise.com </p>'; 
    }
}

// Register shortcode
add_shortcode('kwsw_smart_widget', 'kwsw_shortcode_callback');
