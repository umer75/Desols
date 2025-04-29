<?php
/**
 * Plugin Name: Desol
 * Description: Custom plugin for managing employee records
 * Version: 1.0.0
 * Author: Umer Raza Cheema
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Define plugin constants
if (!defined('DESOL_PLUGIN_VERSION')) {
    define('DESOL_PLUGIN_VERSION', '1.0.0');
}
if (!defined('DESOL_PLUGIN_DIR')) {
    define('DESOL_PLUGIN_DIR', plugin_dir_path(__FILE__));
}
if (!defined('DESOL_PLUGIN_URL')) {
    define('DESOL_PLUGIN_URL', plugin_dir_url(__FILE__));
}
if (!defined('DESOL_PLUGIN_BASENAME')) {
    define('DESOL_PLUGIN_BASENAME', plugin_basename(__FILE__));
}

spl_autoload_register('desol_autoload_classes');
/**
 * Autoload Woo Merchant classes.
 *
 * @since 1.0.0
 * @param string $className Class name to load.
 * @return string|null Class name if loaded, null otherwise.
 */
function desol_autoload_classes($className)
{
    // Validate class name input
    if (!is_string($className) || empty($className)) {
        return null;
    }

    // Only load our plugin classes
    if (false === strpos($className, 'Desol')) {
        return null;
    }

    // Sanitize class name for file path with fallback
    $sanitized = str_replace('_', '-', strtolower($className));
    $file_name = function_exists('sanitize_file_name') 
        ? sanitize_file_name($sanitized)
        : preg_replace('/[^a-z0-9\-]/', '', $sanitized);
    
    // Define possible file locations
    $files = array(
        DESOL_PLUGIN_DIR . 'classes/class-' . $file_name . '.php',
    );

    // Safely include the file if it exists
    foreach ($files as $file) {
        if (file_exists($file) && is_readable($file)) {
            require_once $file;
            break;
        }
    }

    return $className;
}


/**
 * Initialize the Woo Merchant plugin.
 *
 * @since 1.0.0
 * @return Desol_Load Plugin instance.
 */
function desol_init() {
    return Desol_Load::instance();
}
new Desol_Load();
//add_action('init', 'desol_init');
