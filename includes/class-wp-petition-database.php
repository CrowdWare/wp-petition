<?php
/**
 * Database operations for the plugin.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * Database operations for the plugin.
 *
 * This class handles all database operations for the plugin.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Database {

    /**
     * The table name for campaigns.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $campaigns_table    The table name for campaigns.
     */
    private $campaigns_table;

    /**
     * The table name for donations.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $donations_table    The table name for donations.
     */
    private $donations_table;

    /**
     * The table name for votes.
     *
     * @since    1.0.0
     * @access   private
     * @var      string    $votes_table    The table name for votes.
     */
    private $votes_table;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     */
    public function __construct() {
        global $wpdb;
        $this->campaigns_table = $wpdb->prefix . 'petition_campaigns';
        $this->donations_table = $wpdb->prefix . 'petition_donations';
        $this->votes_table = $wpdb->prefix . 'petition_votes';
    }

    /**
     * Create a new campaign.
     *
     * @since    1.0.0
     * @param    array    $campaign_data    The campaign data.
     * @return   int|false                  The campaign ID on success, false on failure.
     */
    public function create_campaign($campaign_data) {
        global $wpdb;
        
$result = $wpdb->insert(
    $this->campaigns_table,
    array_merge(
        $campaign_data,
        array('goal_votes' => isset($campaign_data['goal_votes']) ? $campaign_data['goal_votes'] : 0)
    )
);
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Update an existing campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id      The campaign ID.
     * @param    array    $campaign_data    The campaign data.
     * @return   bool                       True on success, false on failure.
     */
    public function update_campaign($campaign_id, $campaign_data) {
        global $wpdb;
        
$result = $wpdb->update(
    $this->campaigns_table,
    array_merge(
        $campaign_data,
        array('goal_votes' => isset($campaign_data['goal_votes']) ? $campaign_data['goal_votes'] : 0)
    ),
    array('campaign_id' => $campaign_id)
);
        
        return $result !== false;
    }

    /**
     * Get a campaign by ID.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   object|null              The campaign object, or null if not found.
     */
    public function get_campaign($campaign_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->campaigns_table} WHERE campaign_id = %d",
                $campaign_id
            )
        );
    }

    /**
     * Get a campaign by page ID.
     *
     * @since    1.0.0
     * @param    int      $page_id    The page ID.
     * @return   object|null          The campaign object, or null if not found.
     */
    public function get_campaign_by_page_id($page_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->campaigns_table} WHERE page_id = %d",
                $page_id
            )
        );
    }

    /**
     * Get all campaigns.
     *
     * @since    1.0.0
     * @return   array    The campaigns.
     */
    public function get_campaigns() {
        global $wpdb;
        
        return $wpdb->get_results(
            "SELECT * FROM {$this->campaigns_table} ORDER BY created_at DESC"
        );
    }

    /**
     * Delete a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   bool                     True on success, false on failure.
     */
    public function delete_campaign($campaign_id) {
        global $wpdb;
        
        // First delete all donations for this campaign
        $wpdb->delete(
            $this->donations_table,
            array('campaign_id' => $campaign_id)
        );
        
        // Then delete the campaign
        $result = $wpdb->delete(
            $this->campaigns_table,
            array('campaign_id' => $campaign_id)
        );
        
        return $result !== false;
    }

    /**
     * Create a new donation.
     *
     * @since    1.0.0
     * @param    array    $donation_data    The donation data.
     * @return   int|false                  The donation ID on success, false on failure.
     */
    public function create_donation($donation_data) {
        global $wpdb;
        
        $result = $wpdb->insert(
            $this->donations_table,
            $donation_data
        );
        
        if ($result) {
            return $wpdb->insert_id;
        }
        
        return false;
    }

    /**
     * Get donations for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The donations.
     */
    public function get_donations_by_campaign($campaign_id) {
        global $wpdb;
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$this->donations_table} WHERE campaign_id = %d AND donation_type = 'hours' ORDER BY created_at DESC",
                $campaign_id
            )
        );
    }

    /**
     * Get total hours donated for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   int                      The total hours donated.
     */
    public function get_total_hours($campaign_id) {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(hours) FROM {$this->donations_table} WHERE campaign_id = %d AND donation_type = 'hours'",
                $campaign_id
            )
        );
    }
    
    /**
     * Get total Minutos donated for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    bool     $only_received  Whether to only count received Minutos.
     * @return   int                      The total Minutos donated.
     */
    public function get_total_minutos($campaign_id, $only_received = false) {
        global $wpdb;
        
        $where = "campaign_id = %d AND donation_type = 'minutos'";
        $params = array($campaign_id);
        
        if ($only_received) {
            $where .= " AND minutos_received = 1";
        }
        
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT SUM(minutos) FROM {$this->donations_table} WHERE $where",
                $params
            )
        );
    }
    
    /**
     * Get monetary value of Minutos donated for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    bool     $only_received  Whether to only count received Minutos.
     * @return   float                    The monetary value of Minutos donated.
     */
    public function get_minutos_monetary_value($campaign_id, $only_received = false) {
        $total_minutos = $this->get_total_minutos($campaign_id, $only_received);
        
        // Convert Minutos to monetary value (2 Minutos = 1 Euro)
        return $total_minutos / 2;
    }

    /**
     * Get total monetary donations for a campaign from Stripe.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    bool     $include_test   Whether to include test mode orders.
     * @return   float                    The total monetary donations.
     */
    public function get_total_monetary_donations($campaign_id, $include_test = true) {
        global $wpdb;
        
        // Check if Stripe integration is enabled in settings
        $stripe_integration = get_option('wp_petition_stripe_integration', 0);
        if (!$stripe_integration) {
            return 0.00;
        }
        
        // Check if Stripe Payments plugin is active
        if (!post_type_exists('stripe_order')) {
            return 0.00;
        }
        
        // Get the campaign to check for associated product IDs
        $campaign = $this->get_campaign($campaign_id);
        if (!$campaign) {
            return 0.00;
        }
        
        // Get the associated Stripe product IDs
        $stripe_product_ids = get_post_meta($campaign_id, 'stripe_product_ids', true);
        if (empty($stripe_product_ids)) {
            return 0.00;
        }
        
        // Convert to array if it's a string
        if (!is_array($stripe_product_ids)) {
            $stripe_product_ids = explode(',', $stripe_product_ids);
        }
        
        // Initialize total amount
        $total_amount = 0.00;
        
        // Query Stripe orders (custom post type: stripe_order)
        $args = array(
            'post_type' => 'stripe_order',
            'post_status' => 'publish',
            'posts_per_page' => -1,
        );
        
        $orders = get_posts($args);
        
        foreach ($orders as $order) {
            // Get order data
            $order_data = get_post_meta($order->ID, 'order_data', true);
            
            if (!$order_data || empty($order_data['product_id'])) {
                continue;
            }
            
            // Check if this order is for one of our associated products
            if (!in_array($order_data['product_id'], $stripe_product_ids)) {
                continue;
            }
            
            // Check if we should include test mode orders
            if (!$include_test && isset($order_data['is_live']) && $order_data['is_live'] == 0) {
                continue;
            }
            
            // Check if the order is paid
            $order_status = get_post_meta($order->ID, 'asp_order_status', true);
            if (empty($order_status)) {
                // Legacy orders might not have a status, check the charge data
                if (isset($order_data['charge']->paid) && $order_data['charge']->paid && 
                    isset($order_data['charge']->captured) && $order_data['charge']->captured) {
                    // Order is paid
                    $total_amount += floatval($order_data['paid_amount']);
                }
            } else if ($order_status === 'paid') {
                // Order has a status and it's paid
                $total_amount += floatval($order_data['paid_amount']);
            }
        }
        
        return $total_amount;
    }

    /**
     * Get donation statistics for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The donation statistics.
     */
    public function get_donation_stats($campaign_id) {
        global $wpdb;
        
        $campaign = $this->get_campaign($campaign_id);
        if (!$campaign) {
            return array(
                'total_hours' => 0,
                'goal_hours' => 0,
                'hours_percentage' => 0,
                'total_amount' => 0,
                'goal_amount' => 0,
                'amount_percentage' => 0,
                'total_donors' => 0,
                'facebook_posts' => 0,
                'x_posts' => 0,
                'total_minutos' => 0,
                'total_minutos_received' => 0,
                'minutos_monetary_value' => 0,
            );
        }
        
        $total_hours = $this->get_total_hours($campaign_id);
        $total_amount = $this->get_total_monetary_donations($campaign_id);
        $total_minutos = $this->get_total_minutos($campaign_id, false);
        $total_minutos_received = $this->get_total_minutos($campaign_id, true);
         
        // Add Minutos monetary value to total amount
        //$total_amount += $minutos_monetary_value;
        
        $total_donors = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(DISTINCT donation_id) FROM {$this->donations_table} WHERE campaign_id = %d",
                $campaign_id
            )
        );
        
        $facebook_posts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->donations_table} WHERE campaign_id = %d AND facebook_post = 1",
                $campaign_id
            )
        );
        
        $x_posts = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->donations_table} WHERE campaign_id = %d AND x_post = 1",
                $campaign_id
            )
        );
        
        $hours_percentage = $campaign->goal_hours > 0 ? ($total_hours / $campaign->goal_hours) * 100 : 0;
        $amount_percentage = $campaign->goal_amount > 0 ? ($total_amount / $campaign->goal_amount) * 100 : 0;
        
        return array(
            'total_hours' => $total_hours,
            'goal_hours' => $campaign->goal_hours,
            'hours_percentage' => min(100, $hours_percentage),
            'total_amount' => $total_amount,
            'goal_amount' => $campaign->goal_amount,
            'amount_percentage' => min(100, $amount_percentage),
            'total_donors' => $total_donors,
            'facebook_posts' => $facebook_posts,
            'x_posts' => $x_posts,
            'total_minutos' => $total_minutos,
            'total_minutos_received' => $total_minutos_received,
            'minutos_monetary_value' => $minutos_monetary_value,
        );
    }

    /**
     * Get total votes for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   int                      The total votes.
     */
    public function get_total_votes($campaign_id) {
        global $wpdb;
        
        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT COUNT(*) FROM {$this->votes_table} WHERE campaign_id = %d",
                $campaign_id
            )
        );
    }
}
