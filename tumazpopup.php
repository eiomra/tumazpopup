<?php
/*
Plugin Name: Tumaz Popup Message
Description: Displays customizable pop-up messages on any page through this shortcode [tumaz_popup_message].
Version: 1.0
Author: Oboyi Thompson
*/

// Enqueue necessary scripts and styles
function pm_enqueue_scripts() {
    wp_enqueue_script('pm-popup-script', plugin_dir_url(__FILE__) . '/js/popup-script.js', array('jquery'), null, true);
    wp_enqueue_style('pm-popup-style', plugin_dir_url(__FILE__) . '/css/popup-style.css');
}
add_action('wp_enqueue_scripts', 'pm_enqueue_scripts');

// Add admin menu
function pm_add_admin_menu() {
    add_menu_page('Tumaz Popup', 'Tumaz Popup', 'manage_options', 'tumaz-popup-messages', 'pm_admin_page', 'dashicons-format-chat', 100);
}
add_action('admin_menu', 'pm_add_admin_menu');

// Admin page content
function pm_admin_page() {
    if (isset($_POST['pm_save_messages'])) {
        check_admin_referer('pm_save_messages_nonce');
        $messages = array();

        if (isset($_POST['pm_messages']) && is_array($_POST['pm_messages'])) {
            foreach ($_POST['pm_messages'] as $message) {
                if (is_array($message) && !empty($message['title']) && !empty($message['message'])) {
                    $messages[] = array(
                        'title' => sanitize_text_field($message['title']),
                        'message' => sanitize_textarea_field($message['message']),
                    );
                }
            }
        }

        update_option('pm_popup_messages', $messages);
        echo '<div class="updated"><p>Messages saved!</p></div>';
    }

    $messages = get_option('pm_popup_messages', array());

    if (!is_array($messages)) {
        $messages = array();
    } else {
        foreach ($messages as $key => $message) {
            if (!is_array($message)) {
                unset($messages[$key]);
            }
        }
    }

    ?>
    <div class="wrap">
        <h1>Tumaz Popup Messages</h1>
        <p>Use this shortcode [tumaz_popup_message] to display the popup in any page</p>
        <form method="POST">
            <?php wp_nonce_field('pm_save_messages_nonce'); ?>
            <table class="form-table" id="pm-messages-table">
                <tr>
                    <th>Title</th>
                    <th>Message</th>
                    <th>Action</th>
                </tr>
                <?php foreach ($messages as $index => $message) : ?>
                <tr>
                    <td><input type="text" name="pm_messages[<?php echo $index; ?>][title]" value="<?php echo esc_attr($message['title']); ?>" /></td>
                    <td><textarea name="pm_messages[<?php echo $index; ?>][message]"><?php echo esc_textarea($message['message']); ?></textarea></td>
                    <td><button type="button" class="button pm-remove-message">Remove</button></td>
                </tr>
                <?php endforeach; ?>
            </table>
            <button type="button" class="button" id="pm-add-message">Add Message</button>
            <p class="submit">
                <input type="submit" name="pm_save_messages" id="pm_save_messages" class="button button-primary" value="Save Messages">
            </p>
        </form>
    </div>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        var addButton = document.getElementById('pm-add-message');
        var table = document.getElementById('pm-messages-table');
        var rowIndex = <?php echo count($messages); ?>;

        addButton.addEventListener('click', function() {
            var row = table.insertRow(-1);
            row.innerHTML = '<td><input type="text" name="pm_messages[' + rowIndex + '][title]" /></td><td><textarea name="pm_messages[' + rowIndex + '][message]"></textarea></td><td><button type="button" class="button pm-remove-message">Remove</button></td>';
            rowIndex++;
        });

        table.addEventListener('click', function(event) {
            if (event.target.classList.contains('pm-remove-message')) {
                var row = event.target.closest('tr');
                row.parentNode.removeChild(row);
            }
        });
    });
    </script>
    <?php
}


// Shortcode to display the pop-up message
function pm_popup_message_shortcode($atts, $content = null) {
    $messages = get_option('pm_popup_messages', array());

    if (!is_array($messages)) {
        $messages = array();
    } else {
        foreach ($messages as $key => $message) {
            if (!is_array($message)) {
                unset($messages[$key]);
            }
        }
    }

    if (empty($messages)) {
        return '<!-- No messages available -->';
    }

    ob_start();
    ?>
    <!-- Popup Shortcode Executed -->
    <div id="pm-popup-container">
        <?php foreach ($messages as $index => $message) : ?>
        <div class="pm-popup" data-index="<?php echo $index; ?>">
            <div class="pm-popup-content">
                <span class="pm-close">&times;</span>
                <span class="tttitle"><?php echo esc_html($message['title']); ?></span><br/>
                <span class="ttmesa"><?php echo esc_html($message['message']); ?></span>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php
    $output = ob_get_clean();
    return $output;
}
add_shortcode('tumaz_popup_message', 'pm_popup_message_shortcode');
?>
