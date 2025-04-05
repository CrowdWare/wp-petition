<?php
/**
 * Plugin Name: WP Petition
 * Plugin URI: https://example.com/wp-petition
 * Description: A WordPress plugin for petition campaigns where users can sign and support your cause.
 * Version: 1.0.14
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
define('WP_PETITION_VERSION', '1.0.14');
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
    $atts = shortcode_atts(array('id' => 0, 'lang' => 'en'), $atts);
    $campaign_id = intval($atts['id']);
    $lang = $atts['lang'];

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
        $translations = array(
            'en' => 'No votes found for this campaign.',
            'de' => 'Keine Stimmen für diese Kampagne gefunden.',
            'es' => 'No se encontraron votos para esta campaña.'
        );
        $message = $translations[$lang] ?? $translations['en'];
        return "<p>" . $message . "</p>";
    }

    // Translations for table headers and content
    $translations = array(
        'en' => array(
            'heading' => 'Last people who signed',
            'name' => 'Name',
            'signed' => 'Signed',
            'type' => 'Type',
            'thoughts' => 'Feature Requests or Thoughts',
            'yes' => 'Yes',
            'no' => 'No'
        ),
        'de' => array(
            'heading' => 'Letzte Unterzeichner',
            'name' => 'Name',
            'signed' => 'Unterzeichnet',
            'type' => 'Typ',
            'thoughts' => 'Funktionswünsche oder Gedanken',
            'yes' => 'Ja',
            'no' => 'Nein'
        ),
        'es' => array(
            'heading' => 'Últimas personas que firmaron',
            'name' => 'Nombre',
            'signed' => 'Firmado',
            'type' => 'Tipo',
            'thoughts' => 'Sugerencias o pensamientos',
            'yes' => 'Sí',
            'no' => 'No'
        )
    );
    $t = $translations[$lang] ?? $translations['en'];

    ob_start(); ?>
    <div class="wp-petition-votes-container" data-campaign-id="<?php echo esc_attr($campaign_id); ?>">
    <h3><?php echo esc_html($t['heading']); ?></h3>
    <table class="wp-petition-donors-table">
        <thead>
            <tr>
                <th><?php echo esc_html($t['name']); ?></th>
                <th><?php echo esc_html($t['signed']); ?></th>
                <th><?php echo esc_html($t['type']); ?></th>
                <th><?php echo esc_html($t['thoughts']); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($results as $row): ?>
                <tr>
                    <td style="min-width: 200px;"><?php echo esc_html($row->name); ?></td>
                    <td style="min-width: 100px;"><?php echo $row->interest ? esc_html($t['yes']) : esc_html($t['no']); ?></td>
                    <td style="min-width: 150px;"><?php echo esc_html($row->contribution_role); ?></td>
                    <td><?php echo esc_html($row->notes); ?></td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php
    return ob_get_clean();
}

/**
 * Displays the number of votes for a campaign.
 */
function petition_votes_count($atts) {
    $atts = shortcode_atts(array('id' => 0, 'display' => 'text', 'lang' => 'en'), $atts);
    $campaign_id = intval($atts['id']);
    $lang = $atts['lang'];
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

    // Translations for votes text
    $translations = array(
        'en' => 'Votes',
        'de' => 'Stimmen',
        'es' => 'Votos'
    );
    $votes_text = $translations[$lang] ?? $translations['en'];

    if ($display == 'bar') {
        $percent = ($count / $goal) * 100;
        $percent = min($percent, 100); // Cap at 100%
        return '<div class="wp-petition-progress-bar"><div class="wp-petition-progress-bar-inner" style="width: ' . $percent . '%;"></div><div class="wp-petition-progress-bar-text">' . round($percent, 2) . '%</div></div>';
    } else {
        return "<p>" . esc_html($votes_text) . ": " . esc_html($count) . "</p>";
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
