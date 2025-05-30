<?php
/**
 * Campaign management functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * Campaign management functionality.
 *
 * This class handles all campaign-related operations.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Campaign {

    /**
     * The database handler.
     *
     * @since    1.0.0
     * @access   private
     * @var      WP_Petition_Database    $db    The database handler.
     */
    private $db;

    /**
     * Initialize the class and set its properties.
     *
     * @since    1.0.0
     * @param    WP_Petition_Database    $db    The database handler.
     */
    public function __construct($db) {
        $this->db = $db;
    }

    /**
     * Create a new campaign.
     *
     * @since    1.0.0
     * @param    array    $campaign_data    The campaign data.
     * @return   int|false                  The campaign ID on success, false on failure.
     */
    public function create_campaign($campaign_data) {
        // Validate required fields
        if (empty($campaign_data['title'])) {
            return false;
        }
        
        // Set default values if not provided
        if (!isset($campaign_data['goal_hours'])) {
            $campaign_data['goal_hours'] = 0;
        }
        
        if (!isset($campaign_data['goal_amount'])) {
            $campaign_data['goal_amount'] = 0.00;
        }
        
if (!isset($campaign_data['goal_minutos'])) {
    $campaign_data['goal_minutos'] = 0;
}
if (!isset($campaign_data['goal_votes'])) {
    $campaign_data['goal_votes'] = 0;
}
        
        return $this->db->create_campaign($campaign_data);
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
        // Validate campaign ID
if (empty($campaign_id)) {
    return false;
}
if (!isset($campaign_data['goal_votes'])) {
    $campaign_data['goal_votes'] = 0;
}
        
        return $this->db->update_campaign($campaign_id, $campaign_data);
    }

    /**
     * Get a campaign by ID.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   object|null              The campaign object, or null if not found.
     */
    public function get_campaign($campaign_id) {
        return $this->db->get_campaign($campaign_id);
    }

    /**
     * Get a campaign by page ID.
     *
     * @since    1.0.0
     * @param    int      $page_id    The page ID.
     * @return   object|null          The campaign object, or null if not found.
     */
    public function get_campaign_by_page_id($page_id) {
        return $this->db->get_campaign_by_page_id($page_id);
    }

    /**
     * Get all campaigns.
     *
     * @since    1.0.0
     * @return   array    The campaigns.
     */
    public function get_campaigns() {
        return $this->db->get_campaigns();
    }

    /**
     * Delete a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   bool                     True on success, false on failure.
     */
    public function delete_campaign($campaign_id) {
        return $this->db->delete_campaign($campaign_id);
    }

    /**
     * Format the progress display for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of progress to display ('votes').
     * @return   string                   The formatted progress display.
     */
    public function format_progress_display($campaign_id, $type = 'votes') {
        $stats = $this->get_votes_stats($campaign_id);
        return sprintf(
            '%d von %d Votes',
            $stats['total_votes'],
            $stats['goal_votes']
        );
    }

    /**
     * Generate a progress bar HTML for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of progress to display ('votes').
     * @return   string                   The progress bar HTML.
     */
    public function generate_progress_bar($campaign_id, $type = 'votes') {
        $stats = $this->get_votes_stats($campaign_id);
        $percentage = $stats['votes_percentage'];
        $class = 'votes-progress';
        
        $html = '<div class="wp-petition-progress-container">';
        $html .= '<div class="wp-petition-progress-bar ' . esc_attr($class) . '">';
        $html .= '<div class="wp-petition-progress-fill" style="width: ' . esc_attr($percentage) . '%"></div>';
        $html .= '</div>';
        
        $html .= '<div class="wp-petition-progress-text">';
        $html .= esc_html(sprintf(
            '%d Votes von %d (%.1f%%)',
            $stats['total_votes'],
            $stats['goal_votes'],
            $percentage
        ));
        $html .= '</div>';
        
        $html .= '</div>';
        
        return $html;
    }

    /**
     * Get Votes statistics for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The Votes statistics.
     */
    public function get_votes_stats($campaign_id) {
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return array(
                'total_votes' => 0,
                'goal_votes' => 0,
                'votes_percentage' => 0,
            );
        }
        
        $total_votes = $this->db->get_total_votes($campaign_id);
        
        // Calculate percentage based on goal_votes if available
        $votes_percentage = 0;
        if (isset($campaign->goal_votes) && $campaign->goal_votes > 0) {
            $votes_percentage = ($total_votes / $campaign->goal_votes) * 100;
        }
        
        return array(
            'total_votes' => $total_votes,
            'goal_votes' => isset($campaign->goal_votes) ? $campaign->goal_votes : 0,
            'votes_percentage' => min(100, $votes_percentage),
        );
    }
}
