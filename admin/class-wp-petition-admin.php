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
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    WP_Petition_Database    $db          The database handler.
     * @param    WP_Petition_Campaign    $campaign    The campaign handler.
     */
    public function __construct($db, $campaign) {
        $this->db = $db;
        $this->campaign = $campaign;
        
        // Register AJAX handlers
        
        add_action('wp_ajax_wp_petition_create_campaign', array($this, 'ajax_create_campaign'));
        add_action('wp_ajax_wp_petition_update_campaign', array($this, 'ajax_update_campaign'));
        add_action('wp_ajax_wp_petition_delete_campaign', array($this, 'ajax_delete_campaign'));
        add_action('wp_ajax_wp_petition_update_vote_notes', array($this, 'ajax_update_vote_notes'));
        
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
        
        // Voters submenu
        add_submenu_page(
            'wp-petition',
            __('Voters', 'wp-petition'),
            __('Voters', 'wp-petition'),
            'manage_options',
            'wp-petition-voters',
            array($this, 'display_voters_page')
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
     * Display the voters page.
     *
     * @since    1.0.0
     */
    public function display_voters_page() {
        // Get campaign ID from query string (if any)
        $campaign_id = isset($_GET['campaign_id']) ? intval($_GET['campaign_id']) : 0;
        
        // Get page number
        $page = isset($_GET['paged']) ? max(1, intval($_GET['paged'])) : 1;
        $per_page = 50; // Items per page
        $offset = ($page - 1) * $per_page;
        
        // Get all campaigns for the filter dropdown
        $campaigns = $this->campaign->get_campaigns();
        
        // Get votes with pagination
        $votes = $this->db->get_votes($campaign_id, $offset, $per_page);
        
        // Get total votes count for pagination
        $total_votes = $this->db->get_votes_count($campaign_id);
        $total_pages = ceil($total_votes / $per_page);
        
        // Include the voters page template
        include WP_PETITION_PLUGIN_DIR . 'admin/partials/wp-petition-admin-voters.php';
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
     * AJAX handler for updating vote notes.
     *
     * @since    1.0.0
     */
    public function ajax_update_vote_notes() {
        // Check nonce
        check_ajax_referer('wp_petition_admin_nonce', 'nonce');
        
        // Check permissions
        if (!current_user_can('manage_options')) {
            wp_send_json_error(array('message' => __('You do not have permission to perform this action.', 'wp-petition')));
        }
        
        // Get vote ID and notes
        $vote_id = isset($_POST['vote_id']) ? intval($_POST['vote_id']) : 0;
        $notes = isset($_POST['notes']) ? sanitize_textarea_field($_POST['notes']) : '';
        
        if ($vote_id <= 0) {
            wp_send_json_error(array('message' => __('Invalid vote ID.', 'wp-petition')));
        }
        
        // Update the vote notes
        $result = $this->db->update_vote_notes($vote_id, $notes);
        
        if (!$result) {
            wp_send_json_error(array('message' => __('Failed to update notes.', 'wp-petition')));
        }
        
        wp_send_json_success(array(
            'message' => __('Notes updated successfully.', 'wp-petition'),
        ));
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

}
