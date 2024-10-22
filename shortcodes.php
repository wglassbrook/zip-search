<?php

// Shortcode for displaying the ZIP Code search form
add_shortcode('zip-search', 'zip_search_shortcode');
function zip_search_shortcode() {
    $uid = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 1); // selects a random alpha character as the first character of the string
	$uid .= substr(str_shuffle("0123456789abcdefghijklmnopqrstuvwxyz"), 0, 7);
    ob_start();
    ?>
    <div class="zip-search-container">
        <form id="zip-search-form<?= '-'.$uid;?>" class="mb-4">
            <label for="zipcode-input<?= '-'.$uid;?>" class="form-label sr-only visually-hidden">Zip Code Search</label>
            <div class="input-group input-group-lg">
                <input type="text" id="zipcode-input<?= '-'.$uid;?>" class="form-control" placeholder="Enter Zip Code" required>
                <button type="submit" class="btn btn-primary"><i class="fa fa-solid fa-search" aria-label="Submit"></i><span class="visually-hidden">Search</span></button>
            </div>
        </form>

        <!-- Results will appear here -->
        <div id="zip-results<?= '-'.$uid;?>" class="my-3">
            <p id="zip-result-message<?= '-'.$uid;?>" class="text-center mb-4"></p>
            <ul id="zip-result-list<?= '-'.$uid;?>" class="list-group m-0 mb-4"></ul>
        </div>
    </div>

    <script type="text/javascript">
        jQuery(document).ready(function($) {
            $('#zip-search-form<?= '-'.$uid;?>').on('submit', function(e) {
                e.preventDefault();

                var zipcode = $('#zipcode-input<?= '-'.$uid;?>').val();

                // Clear any previous results before making the AJAX request
                $('#zip-result-message<?= '-'.$uid;?>').empty();
                $('#zip-result-list<?= '-'.$uid;?>').empty();

                // AJAX request to check the zipcode
                $.ajax({
                    url: '<?php echo admin_url('admin-ajax.php'); ?>',
                    type: 'POST',
                    data: {
                        action: 'zip_search_lookup',
                        zipcode: zipcode
                    },
                    success: function(response) {
                        if (response.success) {
                            $('#zip-result-message<?= '-'.$uid;?>').html('The following organizations are associated with ZIP Code <strong>' + zipcode + '</strong>:');
                            // Loop through results and append them to the list
                            $.each(response.data, function(index, item) { 
                                $('#zip-result-list<?= '-'.$uid;?>').append('<li class="d-flex justify-content-between align-items-center list-group-item"><span class="zip-program-name">' + item.organization + '</span> <a target="_blank" class="btn btn-sm btn-primary" href="' + item.url + '">More Info <i class="fa fa-solid fa-angle-right"></i></a></li>');
                            });
                        } else {
                            $('#zip-result-message<?= '-'.$uid;?>').html('Your search for <strong>' + zipcode + '</strong> did not return any results.<br>Please try again with another Michigan based ZIP Code.');
                        }
                    },
                    error: function() {
                        $('#zip-result-message<?= '-'.$uid;?>').html('There was an error processing your request. Please try again.');
                    }
                });
            });
        });
    </script>
    <?php
    return ob_get_clean();
}

// AJAX handler for looking up the ZIP Code
add_action('wp_ajax_zip_search_lookup', 'zip_search_lookup');
add_action('wp_ajax_nopriv_zip_search_lookup', 'zip_search_lookup');

function zip_search_lookup() {
    if (!isset($_POST['zipcode'])) {
        wp_send_json_error(['message' => 'No zipcode provided']);
    }

    $zipcode = sanitize_text_field($_POST['zipcode']);
    $recent_file = zip_search_get_recent_file();

    if (!$recent_file || !file_exists($recent_file)) {
        wp_send_json_error(['message' => 'No CSV file found']);
    }

    $results = [];
    $is_first_row = true; // Track if we are on the first row

    // Open the CSV and search for the zipcode
    if (($handle = fopen($recent_file, 'r')) !== false) {
        while (($data = fgetcsv($handle, 1000, ',')) !== false) {
            if ($is_first_row) {
                // Skip the header row
                $is_first_row = false;
                continue;
            }
            
            if ($data[0] == $zipcode) { // Assuming zipcode is in the first column
                $results[] = [
                    'organization' => $data[1],  // Assuming Organization is in the second column
                    'url' => esc_url($data[2])   // Assuming URL is in the third column
                ];
            }
        }
        fclose($handle);
    }

    if (!empty($results)) {
        wp_send_json_success($results);
    } else {
        wp_send_json_error(['message' => 'ZIP Code not found']);
    }
}