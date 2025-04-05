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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    WP_Petition_Database    $db          The database handler.
     * @param    WP_Petition_Campaign    $campaign    The campaign handler.
     */
    public function __construct($db, $campaign) {
        $this->db = $db;
        $this->campaign = $campaign;
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
        // Diese Methode ist jetzt leer, da die Donation-Funktionalität entfernt wurde
    }

    /**
     * AJAX handler for submitting a donation.
     *
     * @since    1.0.0
     */
    public function ajax_submit_donation() {
        // Diese Methode ist jetzt leer, da die Donation-Funktionalität entfernt wurde
        wp_send_json_error(array('message' => __('Diese Funktion ist nicht mehr verfügbar.', 'wp-petition')));
    }

    /**
     * Shortcode for the donation form.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The donation form HTML.
     */
    public function shortcode_donation_form($atts) {
        // Diese Methode gibt jetzt eine Nachricht zurück, dass die Funktion nicht mehr verfügbar ist
        return '<p>' . __('Diese Funktion ist nicht mehr verfügbar.', 'wp-petition') . '</p>';
    }

    /**
     * Shortcode for the donors list.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The donors list HTML.
     */
    public function shortcode_donors_list($atts) {
        // Diese Methode gibt jetzt eine Nachricht zurück, dass die Funktion nicht mehr verfügbar ist
        return '<p>' . __('Diese Funktion ist nicht mehr verfügbar.', 'wp-petition') . '</p>';
    }

    /**
     * Shortcode for the progress display.
     *
     * @since    1.0.0
     * @param    array    $atts    Shortcode attributes.
     * @return   string            The progress display HTML.
     */
    public function shortcode_progress_display($atts) {
        // Diese Methode gibt jetzt eine Nachricht zurück, dass die Funktion nicht mehr verfügbar ist
        return '<p>' . __('Diese Funktion ist nicht mehr verfügbar.', 'wp-petition') . '</p>';
    }
}
