<?php

// Add the options page to the WordPress admin menu
add_action('admin_menu', 'zip_search_admin_menu');
function zip_search_admin_menu() {
    add_menu_page(
        'Zip Search', // Page title
        'Zip Search', // Menu title
        'manage_options', // Capability
        'zip-search', // Menu slug
        'zip_search_admin_page', // Callback function
        'dashicons-search' // Icon
    );
}

// Admin Page: Handles CSV upload and displays table
function zip_search_admin_page() {
    if (!current_user_can('manage_options')) {
        return;
    }

    echo '<div class="wrap">';
    echo '<h1>Zip Search</h1>';

    // Handle file upload
    if (isset($_POST['upload_csv']) && !empty($_FILES['zip_csv']['tmp_name'])) {
        $file = $_FILES['zip_csv'];
        $upload_result = zip_search_upload_csv($file);

        if ($upload_result['status'] === 'success') {
            echo '<div class="notice notice-success"><p>File uploaded successfully: ' . $upload_result['filename'] . '</p></div>';
        } else {
            echo '<div class="notice notice-error"><p>' . $upload_result['message'] . '</p></div>';
        }
    }

    // Show file upload form
    echo '<form method="post" enctype="multipart/form-data">';
    echo '<input type="file" name="zip_csv" accept=".csv" required>';
    echo '<input type="submit" name="upload_csv" class="button button-primary" value="Upload CSV">';
    echo '</form>';

    // Display the most recent CSV file and table
    $recent_file = zip_search_get_recent_file();
    if ($recent_file) {
        echo '<h2>Most Recent File: ' . basename($recent_file) . '</h2>';
        echo zip_search_display_csv_table($recent_file);
    }

    echo '</div>';
}


// Function to upload CSV file
function zip_search_upload_csv($file) {
    $upload_dir = ZIP_SEARCH_UPLOAD_DIR;
    $filename = sanitize_file_name($file['name']);
    $filepath = $upload_dir . '/' . $filename;

    // Check if the uploaded file is a valid CSV
    $file_type = wp_check_filetype($filename);
    if ($file_type['ext'] !== 'csv') {
        return ['status' => 'error', 'message' => 'Please upload a valid CSV file.'];
    }

    // Move uploaded file to the uploads directory
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['status' => 'error', 'message' => 'Failed to upload the file.'];
    }

    return ['status' => 'success', 'filename' => $filename];
}

// Get the most recent CSV file from the directory
function zip_search_get_recent_file() {
    $upload_dir = ZIP_SEARCH_UPLOAD_DIR;
    if (!file_exists($upload_dir)) {
        return false;
    }

    $files = glob($upload_dir . '/*.csv');
    if (empty($files)) {
        return false;
    }

    // Get the most recent file by modification date
    usort($files, function($a, $b) {
        return filemtime($b) - filemtime($a);
    });

    return $files[0];
}

// Output the CSV data in an HTML table for the admin optoion page.
function zip_search_display_csv_table($file) {
    if (!file_exists($file)) {
        return '<p>No CSV file found.</p>';
    }

    $html = '<h3>CSV Data</h3>';
    $html .= '<table class="widefat fixed striped">';
    $html .= '<thead><tr><th style="width:32px;"></th><th>Zip</th><th>Organization</th><th>URL</th></tr></thead>';
    $html .= '<tbody>';

    if (($handle = fopen($file, 'r')) !== false) {
        $is_first_row = true; // Track if we are on the first row
        $i = 0;

        while (($data = fgetcsv($handle, 1000, ',')) !== false) { 
            if ($is_first_row) {
                // Skip the header row
                $is_first_row = false;
                continue;
            }
            $i++;
            $html .= '<tr>';
            $html .= '<td style="width:32px;">' . $i . '</td>';
            $html .= '<td>' . esc_html($data[0]) . '</td>';
            $html .= '<td>' . esc_html($data[1]) . '</td>';
            $html .= '<td><a href="' . esc_html($data[2]) . '" target="_blank">' . esc_html($data[2]) . '</a></td>';

            $html .= '</tr>';
        }
        fclose($handle);
    }

    $html .= '</tbody></table>';

    return $html;
}

// Function to add help tab
function zip_search_add_help_tab() {
    $screen = get_current_screen();

    // Check if the current screen is the correct admin page
    if ($screen->id !== 'toplevel_page_zip-search') {
        return;
    }

    // Add the Help Tab
    $screen->add_help_tab([
        'id'      => 'csv_file_upload',
        'title'   => 'CSV File Upload',
        'content' => '<p>Using the form field bellow, choose and upload a pre-formatted CSV file with your ZIP Code data.</p>'
    ]);
    $screen->add_help_tab([
        'id'      => 'csv_data_formatting',
        'title'   => 'CSV Data Formatting',
        'content' => '<p>When creating the CSV for data uploads, please adhere to the following rules:</p>
                        <ul style="list-style:disc; margin-left: 1rem;">
                            <li>Your CSV file must be comma separated (Default when exporting from Excel).</li>
                            <li>Ensure the three columns are <strong>Zip</strong>, <strong>Organization</strong>, and <strong>URL</strong>.</li>
                            <li>Organization URLs must be the complete URL, including the leading <strong>http://</strong> or <strong>https://</strong>, otherwise the link will result in a 404 error.</li>
                            <li>It\'s okay to have multiple Organizations per ZIP Code; however, this utility does not ensure that your row data is unique. Make sure this is done in the CSV before upload.</li>
                            <li>All data is required. You must have each of the three cells in a row complete (no empty Zip, Organization or URL cells).</li>
                            <li>An example of a valid, pre-formatted CSV file is available to download <a href="'.plugins_url("zip_data_example.csv", __FILE__).'">here</a></li>
                        </ul>'
    ]);
    $screen->add_help_tab([
        'id'      => 'zip_search_output',
        'title'   => 'Zip Search Output',
        'content' => '<p>The search field will display wherever you use the shortcode <strong><tt>[zip-search]</tt></strong> anywhere within your content. Results will display directly under the search field.</p>'
    ]);
}

// Hook the help tab addition to admin head
add_action('admin_head', 'zip_search_add_help_tab');
