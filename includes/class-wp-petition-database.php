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
        
        // Delete the campaign
        $result = $wpdb->delete(
            $this->campaigns_table,
            array('campaign_id' => $campaign_id)
        );
        
        return $result !== false;
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

    /**
     * Get votes for a campaign with pagination.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID. Use 0 for all campaigns.
     * @param    int      $offset         The offset.
     * @param    int      $limit          The limit.
     * @return   array                    The votes.
     */
    public function get_votes($campaign_id = 0, $offset = 0, $limit = 50) {
        global $wpdb;
        
        $where = '';
        $args = array();
        
        if ($campaign_id > 0) {
            $where = 'WHERE campaign_id = %d';
            $args[] = $campaign_id;
        }
        
        $query = "SELECT v.*, c.title as campaign_title 
                 FROM {$this->votes_table} v
                 LEFT JOIN {$this->campaigns_table} c ON v.campaign_id = c.campaign_id
                 $where
                 ORDER BY v.created_at DESC
                 LIMIT %d, %d";
        
        $args[] = $offset;
        $args[] = $limit;
        
        return $wpdb->get_results(
            $wpdb->prepare($query, $args)
        );
    }

    /**
     * Get total votes count (optionally filtered by campaign).
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID. Use 0 for all campaigns.
     * @return   int                      The total votes count.
     */
    public function get_votes_count($campaign_id = 0) {
        global $wpdb;
        
        $where = '';
        $args = array();
        
        if ($campaign_id > 0) {
            $where = 'WHERE campaign_id = %d';
            $args[] = $campaign_id;
        }
        
        $query = "SELECT COUNT(*) FROM {$this->votes_table} $where";
        
        if (!empty($args)) {
            return (int) $wpdb->get_var(
                $wpdb->prepare($query, $args)
            );
        } else {
            return (int) $wpdb->get_var($query);
        }
    }

    /**
     * Get a vote by ID.
     *
     * @since    1.0.0
     * @param    int      $vote_id    The vote ID.
     * @return   object|null          The vote object, or null if not found.
     */
    public function get_vote($vote_id) {
        global $wpdb;
        
        return $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * FROM {$this->votes_table} WHERE votes_id = %d",
                $vote_id
            )
        );
    }

    /**
     * Update vote notes.
     *
     * @since    1.0.0
     * @param    int      $vote_id    The vote ID.
     * @param    string   $notes      The notes.
     * @return   bool                 True on success, false on failure.
     */
    public function update_vote_notes($vote_id, $notes) {
        global $wpdb;
        
        $result = $wpdb->update(
            $this->votes_table,
            array('notes' => $notes),
            array('votes_id' => $vote_id)
        );
        
        return $result !== false;
    }
}
