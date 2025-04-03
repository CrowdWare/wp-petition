<?php
/**
 * The core plugin class.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition {

    /**
     * The loader that's responsible for maintaining and registering all hooks that power
     * the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Petition_Admin    $admin    Maintains and registers all hooks for the admin area.
     */
    protected $admin;

    /**
     * The public-facing functionality of the plugin.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Petition_Public    $public    Maintains and registers all hooks for the public side.
     */
    protected $public;

    /**
     * The database handler.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Petition_Database    $db    The database handler.
     */
    protected $db;

    /**
     * The campaign handler.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Petition_Campaign    $campaign    The campaign handler.
     */
    protected $campaign;

    /**
     * The donation handler.
     *
     * @since    1.0.0
     * @access   protected
     * @var      WP_Petition_Donation    $donation    The donation handler.
     */
    protected $donation;

    /**
     * Define the core functionality of the plugin.
     *
     * @since    1.0.0
     */
    public function __construct() {
        $this->load_dependencies();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    /**
     * Load the required dependencies for this plugin.
     *
     * @since    1.0.0
     * @access   private
     */
private function load_dependencies() {
    // Include the database class
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-database.php';
    $this->db = new WP_Petition_Database();

    // Include the campaign class
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-campaign.php';
    $this->campaign = new WP_Petition_Campaign($this->db);

    // Include the donation class
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-donation.php';
    $this->donation = new WP_Petition_Donation($this->db);

    // Include the admin class
    require_once WP_PETITION_PLUGIN_DIR . 'admin/class-wp-petition-admin.php';
    $this->admin = new WP_Petition_Admin($this->db, $this->campaign, $this->donation);

    // Include the public class
    require_once WP_PETITION_PLUGIN_DIR . 'public/class-wp-petition-public.php';
    $this->public = new WP_Petition_Public($this->db, $this->campaign, $this->donation);

    // Include the updater class
    require_once WP_PETITION_PLUGIN_DIR . 'includes/class-wp-petition-updater.php';
    $this->updater = new WP_Petition_Updater();
}

    /**
     * Register all of the hooks related to the admin area functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_admin_hooks() {
        // Admin menu
        add_action('admin_menu', array($this->admin, 'add_admin_menu'));

        // Admin scripts and styles
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_styles'));
        add_action('admin_enqueue_scripts', array($this->admin, 'enqueue_scripts'));

        // Admin AJAX handlers
        add_action('wp_ajax_wp_petition_create_campaign', array($this->admin, 'ajax_create_campaign'));
        add_action('wp_ajax_wp_petition_update_campaign', array($this->admin, 'ajax_update_campaign'));
        add_action('wp_ajax_wp_petition_delete_campaign', array($this->admin, 'ajax_delete_campaign'));
        add_action('wp_ajax_wp_petition_export_donors', array($this->admin, 'ajax_export_donors'));
        add_action('wp_ajax_wp_petition_mark_minutos_received', array($this->admin, 'ajax_mark_minutos_received'));
    }

    /**
     * Register all of the hooks related to the public-facing functionality
     * of the plugin.
     *
     * @since    1.0.0
     * @access   private
     */
    private function define_public_hooks() {
        // Public scripts and styles
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_styles'));
        add_action('wp_enqueue_scripts', array($this->public, 'enqueue_scripts'));

        // Register shortcodes
        add_shortcode('petition_form', array($this->public, 'shortcode_donation_form'));
        add_shortcode('petition_donors', array($this->public, 'shortcode_donors_list'));
        add_shortcode('petition_progress', array($this->public, 'shortcode_progress_display'));


        // Form submission handler
        add_action('init', array($this->public, 'handle_form_submission'));

        // AJAX handlers for public-facing functionality
        add_action('wp_ajax_wp_petition_submit_donation', array($this->public, 'ajax_submit_donation'));
        add_action('wp_ajax_nopriv_wp_petition_submit_donation', array($this->public, 'ajax_submit_donation'));
    }

    /**
     * Run the plugin.
     *
     * @since    1.0.0
     */
    public function run() { 

        // The plugin is now running
    }
}
