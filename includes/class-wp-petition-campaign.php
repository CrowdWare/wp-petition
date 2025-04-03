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
     * Get donation statistics for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The donation statistics.
     */
    public function get_donation_stats($campaign_id) {
        return $this->db->get_donation_stats($campaign_id);
    }

    /**
     * Get Minutos statistics for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The Minutos statistics.
     */
    public function get_minutos_stats($campaign_id) {
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return array(
                'total_minutos' => 0,
                'total_minutos_received' => 0,
                'minutos_monetary_value' => 0,
                'minutos_received_monetary_value' => 0,
                'goal_amount' => 0,
                'goal_minutos' => 0,
                'minutos_percentage' => 0,
            );
        }
        
        $total_minutos = $this->db->get_total_minutos($campaign_id, false);
        $total_minutos_received = $this->db->get_total_minutos($campaign_id, true);
        $minutos_monetary_value = $this->db->get_minutos_monetary_value($campaign_id, false);
        $minutos_received_monetary_value = $this->db->get_minutos_monetary_value($campaign_id, true);
        
        // Calculate percentage based on goal_minutos if available, otherwise use goal_amount
        $minutos_percentage = 0;
        if (isset($campaign->goal_minutos) && $campaign->goal_minutos > 0) {
            $minutos_percentage = ($total_minutos / $campaign->goal_minutos) * 100;
        } elseif ($campaign->goal_amount > 0) {
            $minutos_percentage = ($minutos_monetary_value / $campaign->goal_amount) * 100;
        }
        
        return array(
            'total_minutos' => $total_minutos,
            'total_minutos_received' => $total_minutos_received,
            'minutos_monetary_value' => $minutos_monetary_value,
            'minutos_received_monetary_value' => $minutos_received_monetary_value,
            'goal_amount' => $campaign->goal_amount,
            'goal_minutos' => isset($campaign->goal_minutos) ? $campaign->goal_minutos : 0,
            'minutos_percentage' => min(100, $minutos_percentage),
        );
    }
    /**
     * Format the progress display for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of progress to display ('hours', 'money', or 'minutos').
     * @return   string                   The formatted progress display.
     */
    public function format_progress_display($campaign_id, $type = 'hours') {
        if ($type === 'hours') {
            $stats = $this->get_donation_stats($campaign_id);
            return sprintf(
                '%d von %d Stunden',
                $stats['total_hours'],
                $stats['goal_hours']
            );
        } elseif ($type === 'minutos') {
            $stats = $this->get_minutos_stats($campaign_id);
            if ($stats['goal_minutos'] > 0) {
                return sprintf(
                    '%d Minutos von %d',
                    $stats['total_minutos'],
                    $stats['goal_minutos']
                );
            } else {
                return sprintf(
                    '%d Minutos (%.2f,- €) von %.2f,- €',
                    $stats['total_minutos'],
                    $stats['minutos_monetary_value'],
                    $stats['goal_amount']
                );
            }
        } else {
            $stats = $this->get_donation_stats($campaign_id);
            return sprintf(
                '%.2f,- € von %.2f,- €',
                $stats['total_amount'],
                $stats['goal_amount']
            );
        }
    }

    /**
     * Generate a progress bar HTML for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of progress to display ('hours', 'money', or 'minutos').
     * @return   string                   The progress bar HTML.
     */
    public function generate_progress_bar($campaign_id, $type = 'hours') {
        if ($type === 'hours') {
            $stats = $this->get_donation_stats($campaign_id);
            $percentage = $stats['hours_percentage'];
            $class = 'hours-progress';
            
            $html = '<div class="wp-petition-progress-container">';
            $html .= '<div class="wp-petition-progress-bar ' . esc_attr($class) . '">';
            $html .= '<div class="wp-petition-progress-fill" style="width: ' . esc_attr($percentage) . '%"></div>';
            $html .= '</div>';
            
            $html .= '<div class="wp-petition-progress-text">';
            $html .= esc_html(sprintf(
                '%d von %d Stunden (%.1f%%)',
                $stats['total_hours'],
                $stats['goal_hours'],
                $percentage
            ));
            $html .= '</div>';
            
            $html .= '</div>';
            
            return $html;
        } elseif ($type === 'minutos') {
            $stats = $this->get_minutos_stats($campaign_id);
            $percentage = $stats['minutos_percentage'];
            $class = 'minutos-progress';
            
            $html = '<div class="wp-petition-progress-container">';
            $html .= '<div class="wp-petition-progress-bar ' . esc_attr($class) . '">';
            $html .= '<div class="wp-petition-progress-fill" style="width: ' . esc_attr($percentage) . '%"></div>';
            $html .= '</div>';
            
            $html .= '<div class="wp-petition-progress-text">';
            
            if ($stats['goal_minutos'] > 0) {
                $html .= esc_html(sprintf(
                    '%d Minutos von %d (%.1f%%)',
                    $stats['total_minutos'],
                    $stats['goal_minutos'],
                    $percentage
                ));
            } else {
                $html .= esc_html(sprintf(
                    '%d Minutos (%.2f,- €) von %.2f,- € (%.1f%%)',
                    $stats['total_minutos'],
                    $stats['minutos_monetary_value'],
                    $stats['goal_amount'],
                    $percentage
                ));
            }
            
            $html .= '</div>';
            
            $html .= '</div>';
            
            return $html;
        } elseif ($type === 'votes') {
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
        } else {
            $stats = $this->get_donation_stats($campaign_id);
            $percentage = $stats['amount_percentage'];
            $class = 'money-progress';
            
            $html = '<div class="wp-petition-progress-container">';
            $html .= '<div class="wp-petition-progress-bar ' . esc_attr($class) . '">';
            $html .= '<div class="wp-petition-progress-fill" style="width: ' . esc_attr($percentage) . '%"></div>';
            $html .= '</div>';
            
            $html .= '<div class="wp-petition-progress-text">';
            $html .= esc_html(sprintf(
                '%.2f,- € von %.2f,- € (%.1f%%)',
                $stats['total_amount'],
                $stats['goal_amount'],
                $percentage
            ));
            $html .= '</div>';
            
            $html .= '</div>';
            
            return $html;
        }
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
