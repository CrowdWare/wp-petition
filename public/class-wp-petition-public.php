<?php
/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/public
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Public {

    /**
     * The database handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      WP_Petition_Database    $db    The database handler.
     */
    private $db;

    /**
     * The campaign handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      WP_Petition_Campaign    $campaign    The campaign handler.
     */
    private $campaign;

    /**
     * The donation handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      WP_Petition_Donation    $donation    The donation handler.
     */
    private $donation;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    WP_Petition_Database    $db          The database handler.
     * @param    WP_Petition_Campaign    $campaign    The campaign handler.
     * @param    WP_Petition_Donation    $donation    The donation handler.
     */
    public function __construct($db, $campaign, $donation) {
        $this->db = $db;
        $this->campaign = $campaign;
        $this->donation = $donation;
    }

    /**
     * Register the stylesheets for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wp-petition-public',
            WP_PETITION_PLUGIN_URL . 'public/css/wp-petition-public.css',
            array(),
            WP_PETITION_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the public-facing side of the site.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-petition-public',
            WP_PETITION_PLUGIN_URL . 'public/js/wp-petition-public.js',
            array('jquery'),
            WP_PETITION_VERSION,
            false
        );
        
        wp_localize_script(
            'wp-petition-public',
            'wp_petition_public',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_petition_public_nonce'),
            )
        );
    }

    /**
     * Handle form submission.
     *
     * @since    1.0.0
     */
    public function handle_form_submission() {
        if (!isset($_POST['wp_petition_submit']) || !isset($_POST['wp_petition_nonce'])) {
            return;
        }
        
        // Verify nonce
        if (!wp_verify_nonce($_POST['wp_petition_nonce'], 'wp_petition_donation_form')) {
            wp_die(__('Security check failed.', 'wp-petition'));
        }
        
        // Get form data
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $facebook_post = isset($_POST['facebook_post']) ? 1 : 0;
        $x_post = isset($_POST['x_post']) ? 1 : 0;
        $other_support = isset($_POST['other_support']) ? sanitize_textarea_field($_POST['other_support']) : '';
        $donation_type = isset($_POST['donation_type']) ? sanitize_text_field($_POST['donation_type']) : 'hours';
        
        // Prepare form data
        $form_data = array(
            'campaign_id' => $campaign_id,
            'name' => $name,
            'email' => $email,
            'facebook_post' => $facebook_post,
            'x_post' => $x_post,
            'other_support' => $other_support,
            'donation_type' => $donation_type,
        );
        
        // Add hours or minutos based on donation type
        if ($donation_type === 'hours') {
            $form_data['hours'] = isset($_POST['hours']) ? intval($_POST['hours']) : 0;
        } elseif ($donation_type === 'minutos') {
            $form_data['minutos'] = isset($_POST['minutos']) ? intval($_POST['minutos']) : 0;
        }
        
        // Process the donation
        $result = $this->donation->process_donation($form_data);
        
        if (is_wp_error($result)) {
            // Redirect back with error
            wp_redirect(add_query_arg(array(
                'wp_petition_error' => urlencode($result->get_error_message()),
            ), wp_get_referer()));
            exit;
        }
        
        // Redirect back with success message
        wp_redirect(add_query_arg(array(
            'wp_petition_success' => 1,
        ), wp_get_referer()));
        exit;
    }

    /**
     * AJAX handler for submitting a donation.
     *
     * @since    1.0.0
     */
    public function ajax_submit_donation() {
        // Check nonce
        check_ajax_referer('wp_petition_public_nonce', 'nonce');
        
        // Get form data
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        $name = isset($_POST['name']) ? sanitize_text_field($_POST['name']) : '';
        $email = isset($_POST['email']) ? sanitize_email($_POST['email']) : '';
        $facebook_post = isset($_POST['facebook_post']) ? 1 : 0;
        $x_post = isset($_POST['x_post']) ? 1 : 0;
        $other_support = isset($_POST['other_support']) ? sanitize_textarea_field($_POST['other_support']) : '';
        $donation_type = isset($_POST['donation_type']) ? sanitize_text_field($_POST['donation_type']) : 'hours';
        
        // Prepare form data
        $form_data = array(
            'campaign_id' => $campaign_id,
            'name' => $name,
            'email' => $email,
            'facebook_post' => $facebook_post,
            'x_post' => $x_post,
            'other_support' => $other_support,
            'donation_type' => $donation_type,
        );
        
        // Add hours or minutos based on donation type
        if ($donation_type === 'hours') {
            $form_data['hours'] = isset($_POST['hours']) ? intval($_POST['hours']) : 0;
        } elseif ($donation_type === 'minutos') {
            $form_data['minutos'] = isset($_POST['minutos']) ? intval($_POST['minutos']) : 0;
        }
        
        // Process the donation
        $result = $this->donation->process_donation($form_data);
        
        if (is_wp_error($result)) {
            wp_send_json_error(array('message' => $result->get_error_message()));
        }
        
        wp_send_json_success(array(
            'message' => __('Thank you for your donation!', 'wp-petition'),
            'donation_id' => $result,
        ));
    }

    /**
     * Shortcode for the donation form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The donation form HTML.
     */
    public function shortcode_donation_form($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
                'type' => 'hours', // 'hours' or 'minutos'
            ),
            $atts,
            'petition_form'
        );
        
        $campaign_id = intval($atts['id']);
        
        if ($campaign_id <= 0) {
            return '<p>' . __('Invalid campaign ID.', 'wp-petition') . '</p>';
        }
        
        $type = $atts['type'];
        if (!in_array($type, array('hours', 'minutos'))) {
            $type = 'hours';
        }
        
        return $this->donation->generate_donation_form($campaign_id, $type);
    }

    /**
     * Shortcode for the donors list.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The donors list HTML.
     */
    public function shortcode_donors_list($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
                'type' => 'hours', // 'hours', 'money', 'minutos', or 'both'
                'include_test' => 'yes', // 'yes' or 'no'
            ),
            $atts,
            'petition_donors'
        );
        
        $campaign_id = intval($atts['id']);
        
        if ($campaign_id <= 0) {
            return '<p>' . __('Invalid campaign ID.', 'wp-petition') . '</p>';
        }
        
        $type = $atts['type'];
        if (!in_array($type, array('hours', 'money', 'minutos', 'both'))) {
            $type = 'hours';
        }
        
        // Check if Stripe integration is enabled when requesting money donations
        if (($type === 'money' || $type === 'both') && !get_option('wp_petition_stripe_integration', 0)) {
            if ($type === 'money') {
                return '<p>' . __('Stripe integration is not enabled in the settings.', 'wp-petition') . '</p>';
            }
            // If 'both' was requested but Stripe is disabled, fall back to just time and minutos donations
            $type = ($type === 'both') ? 'time_minutos' : 'hours';
        }
        
        return $this->donation->generate_donors_list($campaign_id, $type);
    }

    /**
     * Shortcode for the progress display.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The progress display HTML.
     */
    public function shortcode_progress_display($atts) {
        $atts = shortcode_atts(
            array(
                'id' => 0,
                'type' => 'hours', // 'hours', 'money', or 'minutos'
                'display' => 'bar', // 'bar' or 'text'
            ),
            $atts,
            'petition_progress'
        );
        
        $campaign_id = intval($atts['id']);
        
        if ($campaign_id <= 0) {
            return '<p>' . __('Invalid campaign ID.', 'wp-petition') . '</p>';
        }
        
        $type = $atts['type'];
        if (!in_array($type, array('hours', 'money', 'minutos'))) {
            $type = 'hours';
        }
        
        $display = $atts['display'] === 'text' ? 'text' : 'bar';
        
        // Check if Stripe integration is enabled when requesting money progress
        if ($type === 'money' && !get_option('wp_petition_stripe_integration', 0)) {
            return '<p>' . __('Stripe integration is not enabled in the settings.', 'wp-petition') . '</p>';
        }
        
        if ($display === 'text') {
            return $this->campaign->format_progress_display($campaign_id, $type);
        } else {
            return $this->campaign->generate_progress_bar($campaign_id, $type);
        }
    }
}
