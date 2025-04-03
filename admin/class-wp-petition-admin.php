<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/admin
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Admin {

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
        
        // Register AJAX handlers
        
        add_action('wp_ajax_wp_petition_create_campaign', array($this, 'ajax_create_campaign'));
        add_action('wp_ajax_wp_petition_update_campaign', array($this, 'ajax_update_campaign'));
        add_action('wp_ajax_wp_petition_delete_campaign', array($this, 'ajax_delete_campaign'));
        add_action('wp_ajax_wp_petition_export_donors', array($this, 'ajax_export_donors'));
        add_action('wp_ajax_wp_petition_mark_minutos_received', array($this, 'ajax_mark_minutos_received'));
        
    }

    /**
     * Register the stylesheets for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_styles() {
        wp_enqueue_style(
            'wp-petition-admin',
            WP_PETITION_PLUGIN_URL . 'admin/css/wp-petition-admin.css',
            array(),
            WP_PETITION_VERSION,
            'all'
        );
    }

    /**
     * Register the JavaScript for the admin area.
     *
     * @since    1.0.0
     */
    public function enqueue_scripts() {
        wp_enqueue_script(
            'wp-petition-admin',
            WP_PETITION_PLUGIN_URL . 'admin/js/wp-petition-admin.js',
            array('jquery'),
            WP_PETITION_VERSION,
            false
        );
        
        wp_localize_script(
            'wp-petition-admin',
            'wp_petition_admin',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('wp_petition_admin_nonce'),
            )
        );
    }

    /**
     * Add the admin menu.
     *
     * @since    1.0.0
     */
    public function add_admin_menu() {
        // Main menu
        add_menu_page(
            __('WP Petition', 'wp-petition'),
            __('Petition', 'wp-petition'),
            'manage_options',
            'wp-petition',
            array($this, 'display_campaigns_page'),
            'dashicons-money-alt',
            30
        );
        
        // Campaigns submenu
        add_submenu_page(
            'wp-petition',
            __('Campaigns', 'wp-petition'),
            __('Campaigns', 'wp-petition'),
            'manage_options',
            'wp-petition',
            array($this, 'display_campaigns_page')
        );
        
        // Add campaign submenu
        add_submenu_page(
            'wp-petition',
            __('Add Campaign', 'wp-petition'),
            __('Add Campaign', 'wp-petition'),
            'manage_options',
            'wp-petition-add-campaign',
            array($this, 'display_add_campaign_page')
        );
        
        // Settings submenu
        add_submenu_page(
            'wp-petition',
            __('Settings', 'wp-petition'),
            __('Settings', 'wp-petition'),
            'manage_options',
            'wp-petition-settings',
            array($this, 'display_settings_page')
        );
    }

    /**
     * Display the campaigns page.
     *
     * @since    1.0.0
     */
    public function display_campaigns_page() {
        // Get all campaigns
        $campaigns = $this->campaign->get_campaigns();
        
        // Include the campaigns page template
        include WP_PETITION_PLUGIN_DIR . 'admin/partials/wp-petition-admin-campaigns.php';
    }

    /**
     * Display the add campaign page.
     *
     * @since    1.0.0
     */
    public function display_add_campaign_page() {
        // Check if we're editing an existing campaign
        $campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
        $campaign = null;
        
        if ($campaign_id > 0) {
            $campaign = $this->campaign->get_campaign($campaign_id);
        }
        
        // Include the add campaign page template
        include WP_PETITION_PLUGIN_DIR . 'admin/partials/wp-petition-admin-add-campaign.php';
    }

    /**
     * Display the settings page.
     *
     * @since    1.0.0
     */
    public function display_settings_page() {
        // Include the settings page template
        include WP_PETITION_PLUGIN_DIR . 'admin/partials/wp-petition-admin-settings.php';
    }

    /**
     * AJAX handler for creating a campaign.
     *
     * @since    1.0.0
     */
    public function ajax_create_campaign() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get form data
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
        $goal_hours = isset($_POST['goal_hours']) ? intval($_POST['goal_hours']) : 0;
        $goal_amount = isset($_POST['goal_amount']) ? floatval($_POST['goal_amount']) : 0.00;
        $goal_minutos = isset($_POST['goal_minutos']) ? intval($_POST['goal_minutos']) : 0.00;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        $stripe_product_ids = isset($_POST['stripe_product_ids']) ? $_POST['stripe_product_ids'] : array();
        
        // Validate required fields
        if (empty($title)) {
            wp_send_json_error(array('message' => __('Title is required.', 'wp-petition')));
        }
        
        // Prepare campaign data
        $campaign_data = array(
            'title' => $title,
            'description' => $description,
            'goal_hours' => $goal_hours,
            'goal_amount' => $goal_amount,
            'goal_minutos' => $goal_minutos,
            'start_date' => empty($start_date) ? null : $start_date,
            'end_date' => empty($end_date) ? null : $end_date,
            'page_id' => $page_id > 0 ? $page_id : null,
        );
        
        // Create the campaign
        $campaign_id = $this->campaign->create_campaign($campaign_data);
        
        if (!$campaign_id) {
            wp_send_json_error(array('message' => __('Failed to create campaign.', 'wp-petition')));
        }
        
        // Save Stripe product IDs
        if (!empty($stripe_product_ids)) {
            update_post_meta($campaign_id, 'stripe_product_ids', $stripe_product_ids);
        }

        // Create WooCommerce product
        create_woocommerce_product($campaign_id, $title, $description);
        
        wp_send_json_success(array(
            'message' => __('Campaign created successfully.', 'wp-petition'),
            'campaign_id' => $campaign_id,
        ));
    }

    /**
     * AJAX handler for updating a campaign.
     *
     * @since    1.0.0
     */
    public function ajax_update_campaign() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get campaign ID
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        
        if ($campaign_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid campaign ID.', 'wp-petition')));
        }
        
        // Get form data
        $title = isset($_POST['title']) ? sanitize_text_field($_POST['title']) : '';
        $description = isset($_POST['description']) ? wp_kses_post($_POST['description']) : '';
        $goal_hours = isset($_POST['goal_hours']) ? intval($_POST['goal_hours']) : 0;
        $goal_amount = isset($_POST['goal_amount']) ? floatval($_POST['goal_amount']) : 0.00;
        $goal_minutos = isset($_POST['goal_minutos']) ? intval($_POST['goal_minutos']) : 0;
$goal_votes = isset($_POST['goal_votes']) ? intval($_POST['goal_votes']) : 0;
        $start_date = isset($_POST['start_date']) ? sanitize_text_field($_POST['start_date']) : '';
        $end_date = isset($_POST['end_date']) ? sanitize_text_field($_POST['end_date']) : '';
        $page_id = isset($_POST['page_id']) ? intval($_POST['page_id']) : 0;
        
        // Validate required fields
        if (empty($title)) {
            wp_send_json_error(array('message' => __('Title is required.', 'wp-petition')));
        }
        
        // Prepare campaign data
        $campaign_data = array(
            'title' => $title,
            'description' => $description,
            'goal_hours' => $goal_hours,
            'goal_amount' => $goal_amount,
            'goal_minutos' => $goal_minutos,
'goal_votes' => $goal_votes,
            'start_date' => empty($start_date) ? null : $start_date,
            'end_date' => empty($end_date) ? null : $end_date,
            'page_id' => $page_id > 0 ? $page_id : null,
        );
        
        // Update the campaign
        $result = $this->campaign->update_campaign($campaign_id, $campaign_data);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to update campaign.', 'wp-petition')));
        }
        
        // Save Stripe product IDs
        if (isset($_POST['stripe_product_ids'])) {
            $stripe_product_ids = $_POST['stripe_product_ids'];
            update_post_meta($campaign_id, 'stripe_product_ids', $stripe_product_ids);
        }
        
        wp_send_json_success(array(
            'message' => __('Campaign updated successfully.', 'wp-petition'),
        ));
    }

    /**
     * AJAX handler for deleting a campaign.
     *
     * @since    1.0.0
     */
    public function ajax_delete_campaign() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get campaign ID
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        
        if ($campaign_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid campaign ID.', 'wp-petition')));
        }
        
        // Delete the campaign
        $result = $this->campaign->delete_campaign($campaign_id);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to delete campaign.', 'wp-petition')));
        }
        
        wp_send_json_success(array(
            'message' => __('Campaign deleted successfully.', 'wp-petition'),
        ));
    }

    /**
     * AJAX handler for exporting donors.
     *
     * @since    1.0.0
     */
    public function ajax_export_donors() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get campaign ID
        $campaign_id = isset($_POST['campaign_id']) ? intval($_POST['campaign_id']) : 0;
        
        if ($campaign_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid campaign ID.', 'wp-petition')));
        }
        
        // Generate the PDF export
        $pdf_path = $this->donation->generate_pdf_export($campaign_id);
        
        if (!$pdf_path) {
            wp_send_json_error(array('message' => __('Failed to generate PDF export.', 'wp-petition')));
        }
        
        wp_send_json_success(array(
            'message' => __('PDF export generated successfully.', 'wp-petition'),
            'pdf_url' => $pdf_path,
        ));
    }
    
    /**
     * AJAX handler for marking Minutos as received.
     *
     * @since    1.0.0
     */
    public function ajax_mark_minutos_received() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get donation ID
        $donation_id = isset($_POST['donation_id']) ? intval($_POST['donation_id']) : 0;
        
        if ($donation_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid donation ID.', 'wp-petition')));
        }
        
        // Mark Minutos as received
        $result = $this->donation->mark_minutos_as_received($donation_id);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to mark Minutos as received.', 'wp-petition')));
        }
        
        wp_send_json_success(array(
            'message' => __('Minutos marked as received successfully.', 'wp-petition'),
        ));
    }
}
