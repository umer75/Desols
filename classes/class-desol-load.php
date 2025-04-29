<?php
/**
 * Main plugin loader class.
 *
 * @package desol
 * @since   1.0.0
 */

defined('ABSPATH') || exit;

/**
 * Desol_Load class.
 *
 * Handles plugin initialization
 */
final class Desol_Load
{

    /**
     * The single instance of the class.
     *
     * @var Desol_Load
     * @since 1.0.0
     */
    protected static $instance = null;

    /**
     * Initialize the plugin.
     *
     * @since 1.0.0
     * @return Desol_Load|false 
     */
    public static function instance()
    {
        if (is_null(self::$instance)) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    /**
     * Class constructor.
     *
     * @since 1.0.0
     */
    public function __construct()
    {
        $this->includes();
    }


    /**
     * Include required core files.
     *
     * @since 1.0.0
     */
    public function includes()
    {
        $files = array(
            'includes/helper.php',
        );

        foreach ($files as $file) {
            $file_path = trailingslashit(DESOL_PLUGIN_DIR) . $file;

            if (file_exists($file_path) && is_readable($file_path)) {
                require_once $file_path;
            }
        }

        // Initialize plugin components
        add_action('init', array('Desol_Enqueue', 'init'));
        Desol_Admin_Settings::init();
    }
}
