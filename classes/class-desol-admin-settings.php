<?php
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Handles the employee admin interface including reports and exports
 */
class Desol_Admin_Settings
{
    public function __construct()
    {
        add_action('init', [$this, 'register_employee_post_type']);
        add_action('add_meta_boxes', array($this, 'add_employee_meta_boxes'));
        add_action('save_post', array($this, 'save_employee_meta_data'));
        add_action('admin_menu', [$this, 'add_employee_admin_page']);
        add_action('admin_init', [$this, 'handle_csv_export']);
    }

    /**
     * Initialize the class
     *
     * Instantiates the class and triggers the necessary actions.
     * 
     * @since 1.0.0
     */
    public static function init()
    {
        $DesolClass = __CLASS__;
        new $DesolClass;
    }

    public function register_employee_post_type()
    {
        $labels = [
            'name'               => __('Employees', 'desol'),
            'singular_name'      => __('Employee', 'desol'),
            'menu_name'          => __('Employees', 'desol'),
            'name_admin_bar'     => __('Employee', 'desol'),
            'add_new'            => __('Add New', 'desol'),
            'add_new_item'       => __('Add New Employee', 'desol'),
            'new_item'           => __('New Employee', 'desol'),
            'edit_item'          => __('Edit Employee', 'desol'),
            'view_item'          => __('View Employee', 'desol'),
            'all_items'          => __('All Employees', 'desol'),
            'search_items'       => __('Search Employees', 'desol'),
            'not_found'          => __('No employees found.', 'desol'),
            'not_found_in_trash' => __('No employees found in Trash.', 'desol')
        ];

        $args = [
            'labels'             => $labels,
            'public'             => false,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'menu_position'      => 20,
            'menu_icon'          => 'dashicons-businessperson',
            'supports'           => ['title', 'custom-fields'],
            'capability_type'    => 'post',
            'has_archive'        => false,
            'exclude_from_search' => true,
            'publicly_queryable' => false,
        ];

        register_post_type('employee', $args);
    }

    public function add_employee_meta_boxes()
    {
        add_meta_box(
            'employee_details',
            __('Employee Details', 'desol'),
            array($this, 'render_employee_meta_box'),
            'employee',
            'normal',
            'high'
        );
    }

    public function render_employee_meta_box($post)
    {
        wp_nonce_field('employee_meta_box', 'employee_meta_box_nonce');

        $name = get_post_meta($post->ID, '_employee_name', true);
        $position = get_post_meta($post->ID, '_employee_position', true);
        $email = get_post_meta($post->ID, '_employee_email', true);
        $hire_date = get_post_meta($post->ID, '_employee_hire_date', true);
        $salary = get_post_meta($post->ID, '_employee_salary', true);
?>
        <div class="employee-fields">
            <p>
                <label for="employee_name"><?php _e('Full Name:', 'desol'); ?></label>
                <input type="text" id="employee_name" name="employee_name" value="<?php echo esc_attr($name); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="employee_position"><?php _e('Position:', 'desol'); ?></label>
                <input type="text" id="employee_position" name="employee_position" value="<?php echo esc_attr($position); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="employee_email"><?php _e('Email:', 'desol'); ?></label>
                <input type="email" id="employee_email" name="employee_email" value="<?php echo esc_attr($email); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="employee_hire_date"><?php _e('Date of Hire:', 'desol'); ?></label>
                <input type="date" id="employee_hire_date" name="employee_hire_date" value="<?php echo esc_attr($hire_date); ?>" style="width: 100%;">
            </p>
            <p>
                <label for="employee_salary"><?php _e('Salary:', 'desol'); ?></label>
                <input type="number" id="employee_salary" name="employee_salary" value="<?php echo esc_attr($salary); ?>" style="width: 100%;" step="0.01">
            </p>
        </div>
    <?php
    }

    public function save_employee_meta_data($post_id)
    {
        if (
            !isset($_POST['employee_meta_box_nonce']) ||
            !wp_verify_nonce($_POST['employee_meta_box_nonce'], 'employee_meta_box')
        ) {
            return;
        }

        if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
            return;
        }

        if (!current_user_can('edit_post', $post_id)) {
            return;
        }

        $fields = array(
            'employee_name' => 'sanitize_text_field',
            'employee_position' => 'sanitize_text_field',
            'employee_email' => 'sanitize_email',
            'employee_hire_date' => 'sanitize_text_field',
            'employee_salary' => 'floatval'
        );

        foreach ($fields as $field => $sanitizer) {
            if (isset($_POST[$field])) {
                $value = call_user_func($sanitizer, $_POST[$field]);
                update_post_meta($post_id, '_' . $field, $value);
            }
        }
    }

    public function add_employee_admin_page()
    {
        add_submenu_page(
            'edit.php?post_type=employee',
            __('Employee Report', 'desol'),
            __('Employee Report', 'desol'),
            'manage_options',
            'employee-report',
            [$this, 'render_employee_report_page']
        );
    }

    public function render_employee_report_page()
    {
        $orderby = isset($_GET['orderby']) && in_array($_GET['orderby'], ['salary', 'hire_date']) ? $_GET['orderby'] : 'hire_date';

        $employees = new WP_Query([
            'post_type'      => 'employee',
            'posts_per_page' => -1,
            'meta_key'       => $orderby === 'salary' ? '_employee_salary' : '_employee_hire_date',
            'orderby'        => 'meta_value',
            'order'          => 'ASC',
        ]);

    ?>
        <div class="wrap">
            <h1><?php _e('Employee Report', 'desol'); ?></h1>
            <div class="wrap-top-inner" style="display:flex; justify-content: space-between; align-items: center;margin: 20px 0;">
                <form method="get">
                    <input type="hidden" name="post_type" value="employee">
                    <input type="hidden" name="page" value="employee-report">
                    <label for="orderby"><?php _e('Sort by:', 'desol'); ?></label>
                    <select name="orderby" id="orderby">
                        <option value="hire_date" <?php selected($orderby, 'hire_date'); ?>><?php _e('Date of Hire', 'desol'); ?></option>
                        <option value="salary" <?php selected($orderby, 'salary'); ?>><?php _e('Salary', 'desol'); ?></option>
                    </select>
                    <input type="submit" class="button" value="<?php _e('Filter', 'desol'); ?>">
                </form>

                <div style="display: flex;align-items: center;">
                    <div style="display: flex;align-items: center;margin-right: 20px;">
                        <button id="get-average-salary" class="button button-secondary"><?php _e('Get Average Salary', 'desol'); ?></button>
                        <span style="margin-left: 10px;"><strong><?php _e('Average Salary:', 'desol'); ?></strong> <span id="average-salary">-</span></span>
                    </div>
                    <!-- Export CSV Form -->
                    <form method="post" action="">
                        <?php wp_nonce_field('export_employee_csv_action', 'export_employee_csv_nonce'); ?>
                        <input type="submit" name="export_employee_csv" class="button button-primary" value="<?php _e('Export CSV', 'desol'); ?>">
                    </form>
                </div>
            </div>

            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php _e('Name', 'desol'); ?></th>
                        <th><?php _e('Position', 'desol'); ?></th>
                        <th><?php _e('Email', 'desol'); ?></th>
                        <th><?php _e('Date of Hire', 'desol'); ?></th>
                        <th><?php _e('Salary', 'desol'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    if ($employees->have_posts()) :
                        while ($employees->have_posts()) :
                            $employees->the_post();
                            echo '<tr>';
                            echo '<td>' . esc_html(get_the_title()) . '</td>';
                            echo '<td>' . esc_html(get_post_meta(get_the_ID(), '_employee_position', true)) . '</td>';
                            echo '<td>' . esc_html(get_post_meta(get_the_ID(), '_employee_email', true)) . '</td>';
                            echo '<td>' . esc_html(get_post_meta(get_the_ID(), '_employee_hire_date', true)) . '</td>';
                            echo '<td>' . number_format((float)get_post_meta(get_the_ID(), '_employee_salary', true), 2) . '</td>';
                            echo '</tr>';
                        endwhile;
                        wp_reset_postdata();
                    else :
                        echo '<tr><td colspan="5">' . __('No employees found.', 'desol') . '</td></tr>';
                    endif;
                    ?>
                </tbody>
            </table>
        </div>
<?php
    }

    public function handle_csv_export()
    {
        if (!is_admin() || !current_user_can('manage_options')) {
            return;
        }

        if (
            isset($_POST['export_employee_csv']) &&
            isset($_POST['export_employee_csv_nonce']) &&
            wp_verify_nonce($_POST['export_employee_csv_nonce'], 'export_employee_csv_action')
        ) {
            global $wpdb;

            $results = $wpdb->get_results("
                SELECT p.ID, p.post_title AS name
                FROM {$wpdb->posts} p
                WHERE p.post_type = 'employee'
                AND p.post_status = 'publish'
            ");

            if (empty($results)) {
                wp_die(__('No employees found.', 'desol'));
            }

            // Set CSV headers
            header('Content-Type: text/csv');
            header('Content-Disposition: attachment; filename="employee_report.csv"');
            header('Pragma: no-cache');
            header('Expires: 0');

            $output = fopen('php://output', 'w');
            fputcsv($output, [__('Name', 'desol'), __('Email', 'desol'), __('Position', 'desol'), __('Salary', 'desol'), __('Date of Hire', 'desol')]);

            foreach ($results as $row) {
                $id = absint($row->ID);
                $email = sanitize_email(get_post_meta($id, '_employee_email', true));
                $position = sanitize_text_field(get_post_meta($id, '_employee_position', true));
                $salary = floatval(get_post_meta($id, '_employee_salary', true));
                $hire_date = sanitize_text_field(get_post_meta($id, '_employee_hire_date', true));

                fputcsv($output, [
                    esc_html($row->name),
                    esc_html($email),
                    esc_html($position),
                    number_format($salary, 2),
                    esc_html($hire_date),
                ]);
            }

            fclose($output);
            exit;
        }
    }
}
