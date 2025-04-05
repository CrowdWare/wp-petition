<?php
/**
 * Plugin Name: WP Petition
 * Plugin URI: https://example.com/wp-petition
 * Description: A WordPress plugin for time-based petition campaigns where users can donate their time instead of money.
 * Version: 1.0.7
 * Author: CrowdWare
 * Author URI: https://example.com
 * Text Domain: wp-petition
 * Domain Path: /languages
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}

// Define plugin constants
define('WP_PETITION_VERSION', '1.0.7');
define('WP_PETITION_PLUGIN_DIR', plugin_dir_path(__FILE__));
define('WP_PETITION_PLUGIN_URL', plugin_dir_url(__FILE__));
define('WP_PETITION_PLUGIN_BASENAME', plugin_basename(__FILE__));


/**
 * The code that runs during plugin activation.
 */
function activate_wp_petition() {
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-activator.php';
    WP_Petition_Activator::activate();
}

/**
 * The code that runs during plugin deactivation.
 */
function deactivate_wp_petition() {
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-deactivator.php';
    WP_Petition_Deactivator::deactivate();
}

register_activation_hook(__FILE__, 'activate_wp_petition');
register_deactivation_hook(__FILE__, 'deactivate_wp_petition');

/**
 * The core plugin class that is used to define internationalization,
 * admin-specific hooks, and public-facing site hooks.
 */
require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition.php';
require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-updater.php';

/**
 * Begins execution of the plugin.
 */
function run_wp_petition() {
    $plugin = new WP_Petition();
    $plugin->run();
}
add_shortcode('petition_vote_form', 'petition_vote_form');
add_shortcode('petition_vote_list', 'petition_vote_list');
add_shortcode('petition_votes_count', 'petition_votes_count');
// Hook the vote form submission handler to init
add_action('init', 'handle_petition_vote_submission');
run_wp_petition();

/**
 * Creates a WooCommerce product for a campaign.
 *
 * @param int $campaign_id The ID of the campaign.
 * @param string $title The title of the campaign.
 * @param string $description The description of the campaign.
 */
function create_woocommerce_product($campaign_id, $title, $description) {
    // Check if WooCommerce is active
    if (!class_exists('WooCommerce')) {
        return;
    }

    // Check if the product already exists
    $product_id = get_post_meta($campaign_id, '_woocommerce_product_id', true);
    if ($product_id) {
        return;
    }

    // Create the product
    $product = new WC_Product_Simple();
    $product->set_name($title);
    $product->set_description($description);
    $product->set_status('publish');
    $product->set_catalog_visibility('hidden');
    $product->set_price(1); // Set a default price of 1
    $product->set_regular_price(1);
    $product->set_sold_individually('yes');

    // Save the product
    $product_id = $product->save();

    // Update the campaign meta with the product ID
    update_post_meta($campaign_id, '_woocommerce_product_id', $product_id);
}


/**
 * Handles the submission of the vote form.
 * Hooked to 'init'.
 */
function handle_petition_vote_submission() {
    // Check if our specific form was submitted
    if (!isset($_POST['petition_vote_submit'])) {
        return;
    }

    // Verify nonce
    if (!isset($_POST['wp_petition_vote_nonce']) || !wp_verify_nonce($_POST['wp_petition_vote_nonce'], 'wp_petition_vote_form')) {
        wp_die('Security check failed.');
    }

    // --- Determine Redirect URL ---
    $redirect_url = home_url(); // Default fallback

    if (isset($_POST['_wp_http_referer'])) {
        $form_path = wp_unslash($_POST['_wp_http_referer']);
        // Ensure the path starts with a slash if it's not empty
        if (!empty($form_path) && strpos($form_path, '/') !== 0) {
            $form_path = '/' . $form_path;
        }
        // Reconstruct the full URL using the site's base URL and the path from the form
        $potential_redirect_url = home_url($form_path);
        // Basic validation: check if it looks like a valid URL after reconstruction
        // Use esc_url_raw for validation/sanitization before using it
        $validated_url = esc_url_raw($potential_redirect_url);
        if (!empty($validated_url)) {
             $redirect_url = $validated_url;
        }
    } else {
        // Fallback to wp_get_referer() if the hidden field is missing
        $referer = wp_get_referer();
        if ($referer) {
            // Sanitize the referer as well
             $validated_referer = esc_url_raw($referer);
             if (!empty($validated_referer)) {
                $redirect_url = $validated_referer;
             }
        }
    }
    // --- End Determine Redirect URL ---


    // Get campaign ID from hidden field
    $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;

    if (!$campaign_id) {
        // Redirect back with error if campaign ID is missing
        wp_redirect(add_query_arg('wp_petition_vote_error', urlencode('Invalid Campaign ID.'), $redirect_url));
        exit;
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "petition_votes";

    $name = sanitize_text_field($_POST['name']);
    $email = sanitize_email($_POST['email']);
    $interest = isset($_POST['interest']) ? 1 : 0;
    $role = sanitize_text_field($_POST['role']);
    $notes = sanitize_textarea_field($_POST['notes']);

    if (!is_email($email)) {
        // Redirect back with error for invalid email
        wp_redirect(add_query_arg('wp_petition_vote_error', urlencode('Please enter a valid email.'), $redirect_url));
        exit;
    } else {
   $inserted = $wpdb->insert($table_name, [
            'campaign_id' => $campaign_id,
            'name' => $name,
            'email' => $email,
            'interest' => $interest,
            'contribution_role' => $role,
            'notes' => $notes
        ]);

        if ($inserted === false) {
            // Redirect back with database error
            wp_redirect(add_query_arg('wp_petition_vote_error', urlencode('Database error. Could not save submission.'), $redirect_url));
            exit;
        } else {
   // Send email notification
            wp_mail(get_option('admin_email'), 'New Signature Received', "A new signature has been submitted for campaign ID $campaign_id.\n\nName: $name\nEmail: $email\nInterest: $interest\nRole: $role\nNotes: $notes");

            // Redirect back with success message (wp_redirect handles sanitization)
            wp_redirect(add_query_arg('wp_petition_vote_success', '1', $redirect_url));
            exit;
        }
    }
}


/**
 * Updated function for voting list.
 */
function petition_vote_list($atts) {
    $atts = shortcode_atts(array('id' => 0), $atts);
    $campaign_id = intval($atts['id']);

    if (!$campaign_id) {
        return "<p style='color:red;'>Campaign ID is required.</p>";
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "petition_votes";
    $results = $wpdb->get_results($wpdb->prepare(
        "SELECT name, email, interest, contribution_role, notes FROM $table_name WHERE campaign_id = %d ORDER BY created_at DESC LIMIT 10",
        $campaign_id
    ));

    if (empty($results)) {
        return "<p>No votes found for this campaign.</p>";
    }

    ob_start(); ?>
    <div class="wp-petition-votes-container" data-campaign-id="<?php echo esc_attr($campaign_id); ?>"> <?php // Add a container div like the donors list ?>
    <h3><?php echo esc_html__('Last people who signed', 'wp-petition'); ?></h3> <?php // Add a heading ?>
    <table class="wp-petition-donors-table"> <?php // Add the CSS class here ?>
        <thead>
            <tr>
                <th>Name</th>
                <th>Signed</th>
                <th>Type</th>
                <th>Feature Requests or Thoughts</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td style="min-width: 200px;"><?php echo esc_html($row->name); ?></td>
                    <td style="min-width: 100px;"><?php echo $row->interest ? 'Yes' : 'No'; ?></td>
                    <td style="min-width: 150px;"><?php echo esc_html($row->contribution_role); ?></td>
                    <td><?php echo esc_html($row->notes); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div> <?php // Close the container div ?>
    <?php
    return ob_get_clean();
}

/**
 * Displays the number of votes for a campaign.
 */
function petition_votes_count($atts) {
    $atts = shortcode_atts(array('id' => 0, 'display' => 'text'), $atts);
    $campaign_id = intval($atts['id']);
	 // Output the campaign ID for debugging
    echo "<!-- Campaign ID: " . esc_html($campaign_id) . " -->";
    $display = sanitize_text_field($atts['display']);

    if (!$campaign_id) {
        return "<p style='color:red;'>Campaign ID is required.</p>";
    }

    global $wpdb;
    $table_name = $wpdb->prefix . "petition_votes";
    $campaigns_table_name = $wpdb->prefix . "petition_campaigns";


   // Get the goal_votes from the campaigns table
    $goal = $wpdb->get_var($wpdb->prepare(
        "SELECT goal_votes FROM  $campaigns_table_name WHERE campaign_id = %d",
        $campaign_id
    ));

	 // If goal_votes is not set or is empty, default to 100
    if (empty($goal)) {
        $goal = 100;
    }

    // Output the goal value for debugging
    echo "<!-- Goal Votes: " . esc_html($goal) . " -->";

    $count = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM $table_name WHERE campaign_id = %d",
        $campaign_id
    ));

    if ($display == 'bar') {
        $percent = ($count / $goal) * 100;
        $percent = min($percent, 100); // Cap at 100%
        return '<div class="wp-petition-progress-bar"><div class="wp-petition-progress-bar-inner" style="width: ' . $percent . '%;"></div><div class="wp-petition-progress-bar-text">' . round($percent, 2) . '%</div></div>';
    } else {
        return "<p>Votes: " . esc_html($count) . "</p>";
    }
}

/**
 * Displays the voting form.
 */
function petition_vote_form($atts) {
    // $atts = shortcode_atts(array('id' => 0), $atts);
    $atts = shortcode_atts(array('id' => 0, 'lang' => 'en'), $atts);
    $lang = $atts['lang'];
    $campaign_id = intval($atts['id']);
    

    if (!$campaign_id) {
        return "<p style='color:red;'>Campaign ID is required.</p>";
    }

    // The processing logic is now handled by handle_petition_vote_submission() hooked to 'init'.
    // This function now only displays the form and handles GET parameter messages.

    $translations = array(
        'en' => array(
            'headline' => '✨ Join the Movement',
            'intro' => "You are not alone. But this only becomes true when we connect.<br>Let us know you’re here. Tell us what moves you.<br>Together, we create what the world needs.",
            'thank_you' => 'Thank you for your support!',
            'name' => '🪶 Your Name:',
            'email' => '📬 Your Email:',
            'would_use' => '🌱 Would you use this in your life or community?',
            'how_contribute' => '🤝 How can you contribute?',
            'feature_requests' => '💡 Feature Requests or Thoughts:',
            'support_manifesto' => '[✔️] I support the YANA Manifesto',
            'submit' => 'Count me in',
            'options' => array('Ideas', 'Energy', 'Writing', 'Tech', 'Organizing')
        ),
        'de' => array(
            'headline' => '✨ Mach mit bei der Bewegung',
            'intro' => "Du bist nicht allein. Aber das wird erst wahr, wenn wir uns verbinden.<br>Lass uns wissen, dass du da bist. Sag uns, was dich bewegt.<br>Gemeinsam erschaffen wir, was die Welt braucht.",
            'thank_you' => 'Danke für deine Unterstützung!',
            'name' => '🪶 Dein Name:',
            'email' => '📬 Deine E-Mail:',
            'would_use' => '🌱 Würdest du das in deinem Leben oder deiner Gemeinschaft nutzen?',
            'how_contribute' => '🤝 Wie kannst du beitragen?',
            'feature_requests' => '💡 Funktionswünsche oder Gedanken:',
            'support_manifesto' => '[✔️] Ich unterstütze das YANA-Manifest',
            'submit' => 'Ich bin dabei',
            'options' => array('Ideen', 'Energie', 'Schreiben', 'Technik', 'Organisation')
        ),
        'es' => array(
            'headline' => '✨ Únete al movimiento',
            'intro' => "No estás solo. Pero esto solo se vuelve verdad cuando nos conectamos.<br>Haznos saber que estás aquí. Cuéntanos qué te mueve.<br>Juntos creamos lo que el mundo necesita.",
            'thank_you' => '¡Gracias por tu apoyo!',
            'name' => '🪶 Tu nombre:',
            'email' => '📬 Tu correo electrónico:',
            'would_use' => '🌱 ¿Lo usarías en tu vida o comunidad?',
            'how_contribute' => '🤝 ¿Cómo puedes contribuir?',
            'feature_requests' => '💡 Sugerencias o pensamientos:',
            'support_manifesto' => '[✔️] Apoyo el Manifiesto YANA',
            'submit' => 'Cuenta conmigo',
            'options' => array('Ideas', 'Energía', 'Escritura', 'Tecnología', 'Organización')
        )
    );
    $t = $translations[$lang] ?? $translations['en'];

    ob_start(); ?>
    <div class="wp-petition-form-container">
    <h3><?php echo esc_html($t['headline']); ?></h3>
    <p><?php echo $t['intro']; ?></p>
    <div class="wp-petition-notice-container">
        <?php
        // Display success message if set
        if ( isset( $_GET['wp_petition_vote_success'] ) ) {
            echo '<div class="wp-petition-notice success">' . esc_html($t['thank_you']) . '</div>';
        }

        // Display error message if set
        if ( isset( $_GET['wp_petition_vote_error'] ) ) {
            echo '<div class="wp-petition-notice error">' . esc_html(urldecode( $_GET['wp_petition_vote_error'] ) ) . '</div>';
        }
        ?>
    </div>
    <form class="wp-petition-form" method="post" action="">
    <?php wp_nonce_field('wp_petition_vote_form', 'wp_petition_vote_nonce'); ?>
    <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign_id); ?>">
    <input type="hidden" name="donation_type" value="vote">
    <?php // Add hidden field for the current URL ?>
    <input type="hidden" name="_wp_http_referer" value="<?php echo esc_url(wp_unslash($_SERVER['REQUEST_URI'])); ?>">
    <div class="form-field">
        <label><?php echo $t['name']; ?></label>
        <input type="text" name="name" required><br>
    </div>
    <div class="form-field">
        <label><?php echo $t['email']; ?></label>
        <input type="email" name="email" required><br>
    </div>
    <div class="form-field">
        <label><?php echo $t['would_use']; ?></label>
        <input type="checkbox" name="interest"><br>
    </div>
    <div class="form-field">
        <label><?php echo $t['how_contribute']; ?></label>
        <select name="role">
            <?php foreach ($t['options'] as $opt): ?>
                <option value="<?php echo esc_attr($opt); ?>"><?php echo esc_html($opt); ?></option>
            <?php endforeach; ?>
        </select><br>
    </div>
    <div class="form-field">
        <label><?php echo $t['feature_requests']; ?></label>
        <textarea name="notes"></textarea><br>
    </div>
    <div class="form-field">
        <label><?php echo $t['support_manifesto']; ?></label>
        <input type="submit" class="submit-button" name="petition_vote_submit" value="<?php echo esc_attr($t['submit']); ?>">
    </div>
    </form>
</div>
    <?php
    return ob_get_clean();
}
