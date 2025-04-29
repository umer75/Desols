# Desol

A Simple WordPress plugin that allows administrators to manage employees, view detailed reports, sort data by salary or hire date, and export employee information to CSV.

---

## 📦 Installation

1. Download the plugin ZIP or clone the repository.
2. Upload the plugin to your WordPress site:
   - Go to **Plugins → Add New → Upload Plugin**
   - Choose the ZIP file and click **Install Now**
3. Activate the plugin through the **Plugins** menu in WordPress.

---

## Where to Find Features

- After activation, a new **Employees** post type will appear in the WordPress admin sidebar.
- You can:
  - Add/edit employees using standard post editor.
  - Enter details like full name, email, position, salary, and hire date.
- Go to **Employees → Employee Report** to:
  - View a sortable table of all employees.
  - Filter employees by salary or hire date.
  - See average salary using the button provided.
  - Export the report to CSV.

---

## Approach and Assumptions

### Custom Post Type
Used `register_post_type()` to manage employees as a separate entity. The post type is set to private (`public: false`) but accessible in the admin (`show_ui: true`).

### Meta Fields
Created meta boxes for storing additional employee data (name, email, salary, etc.) and used post meta to store these values securely. Data is sanitized during save to ensure safety.

### Admin Report Page
A custom admin submenu was added under the "Employees" section. It lists all employees using `WP_Query`, sorted by selected meta values. Averages are intended to be calculated client-side or via AJAX (future enhancement).

### CSV Export
Used `wp_nonce_field` for security and outputted all employee data in CSV format directly to the browser. Chose raw SQL for speed and control during export, with sanitization for output.

### Assumptions
- Only administrators or users with `manage_options` capability can access the report and export features.
- Plugin is only used in the admin area; there is no frontend display.
- Basic styling is sufficient; no custom CSS was added beyond WordPress defaults.

---

## License

GPLv2 or later. Free to use and modify.

---
