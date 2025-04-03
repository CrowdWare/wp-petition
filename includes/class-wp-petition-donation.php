<?php
/**
 * Donation processing functionality.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 */

/**
 * Donation processing functionality.
 *
 * This class handles all donation-related operations.
 *
 * @since      1.0.0
 * @package    WP_Petition
 * @subpackage WP_Petition/includes
 * @author     Your Name <email@example.com>
 */
class WP_Petition_Donation {

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
     * Process a donation form submission.
     *
     * @since    1.0.0
     * @param    array    $form_data    The form data.
     * @return   int|WP_Error           The donation ID on success, WP_Error on failure.
     */
    public function process_donation($form_data) {
        // Determine donation type
        $donation_type = isset($form_data['donation_type']) ? sanitize_text_field($form_data['donation_type']) : 'hours';
        
        // Validate required fields based on donation type
        $required_fields = array('campaign_id', 'name', 'email');
        
        if ($donation_type === 'hours') {
            $required_fields[] = 'hours';
        } elseif ($donation_type === 'minutos') {
            $required_fields[] = 'minutos';
        }
        
        foreach ($required_fields as $field) {
            if (empty($form_data[$field])) {
                return new WP_Error(
                    'missing_required_field',
                    sprintf(__('Missing required field: %s', 'wp-petition'), $field)
                );
            }
        }
        
        // Validate hours or minutos (minimum 1)
        if ($donation_type === 'hours' && (int) $form_data['hours'] < 1) {
            return new WP_Error(
                'invalid_hours',
                __('Hours must be at least 1', 'wp-petition')
            );
        } elseif ($donation_type === 'minutos' && (int) $form_data['minutos'] < 1) {
            return new WP_Error(
                'invalid_minutos',
                __('Minutos must be at least 1', 'wp-petition')
            );
        }
        
        // Validate email
        if (!is_email($form_data['email'])) {
            return new WP_Error(
                'invalid_email',
                __('Invalid email address', 'wp-petition')
            );
        }
        
        // Prepare donation data
        $donation_data = array(
            'campaign_id' => (int) $form_data['campaign_id'],
            'name' => sanitize_text_field($form_data['name']),
            'email' => sanitize_email($form_data['email']),
            'facebook_post' => isset($form_data['facebook_post']) ? 1 : 0,
            'x_post' => isset($form_data['x_post']) ? 1 : 0,
            'other_support' => isset($form_data['other_support']) ? sanitize_textarea_field($form_data['other_support']) : '',
            'donation_type' => $donation_type,
        );
        
        // Add hours or minutos based on donation type
        if ($donation_type === 'hours') {
            $donation_data['hours'] = (int) $form_data['hours'];
            $donation_data['minutos'] = 0;
        } elseif ($donation_type === 'minutos') {
            $donation_data['minutos'] = (int) $form_data['minutos'];
            $donation_data['hours'] = 0;
        }

        // Create the donation
        $donation_id = $this->db->create_donation($donation_data);
        
        if (!$donation_id) {
            return new WP_Error(
                'donation_creation_failed',
                __('Failed to create donation', 'wp-petition')
            );
        }
        
        return $donation_id;
    }

    /**
     * Get donations for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The donations.
     */
    public function get_donations_by_campaign($campaign_id) {
        return $this->db->get_donations_by_campaign($campaign_id);
    }

    /**
     * Generate the HTML for the donation form.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of form to display ('hours' or 'minutos').
     * @return   string                   The donation form HTML.
     */
    public function generate_donation_form($campaign_id, $type = 'hours') {
        // Get the campaign
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return '<p>' . __('Campaign not found.', 'wp-petition') . '</p>';
        }
        
        // Start output buffering
        ob_start();
        
        // Include the appropriate form template based on type
        if ($type === 'minutos') {
            include WP_PETITION_PLUGIN_DIR . 'templates/minutos-form-template.php';
        } else {
            include WP_PETITION_PLUGIN_DIR . 'templates/form-template.php';
        }
        
        // Return the buffered content
        return ob_get_clean();
    }

    /**
     * Get Minutos donations for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The Minutos donations.
     */
    public function get_minutos_donations_by_campaign($campaign_id) {
        global $wpdb;
        $donations_table = $wpdb->prefix . 'petition_donations';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$donations_table} WHERE campaign_id = %d AND donation_type = 'minutos' ORDER BY created_at DESC",
                $campaign_id
            )
        );
    }
    
    /**
     * Get time donations for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   array                    The time donations.
     */
    public function get_time_donations_by_campaign($campaign_id) {
        global $wpdb;
        $donations_table = $wpdb->prefix . 'petition_donations';
        
        return $wpdb->get_results(
            $wpdb->prepare(
                "SELECT * FROM {$donations_table} WHERE campaign_id = %d AND donation_type = 'hours' ORDER BY created_at DESC",
                $campaign_id
            )
        );
    }
    
    /**
     * Mark a Minutos donation as received.
     *
     * @since    1.0.0
     * @param    int      $donation_id    The donation ID.
     * @return   bool                     True on success, false on failure.
     */
    public function mark_minutos_as_received($donation_id) {
        global $wpdb;
        $donations_table = $wpdb->prefix . 'petition_donations';
        
        return $wpdb->update(
            $donations_table,
            array(
                'minutos_received' => 1,
                'minutos_received_date' => current_time('mysql')
            ),
            array('donation_id' => $donation_id)
        ) !== false;
    }
    
    /**
     * Generate the HTML for the donors list.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    string   $type           The type of donations to display ('hours', 'money', or 'both').
     * @return   string                   The donors list HTML.
     */
    public function generate_donors_list($campaign_id, $type = 'hours') {
        // Get the campaign
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return '<p>' . __('Campaign not found.', 'wp-petition') . '</p>';
        }
        
        // Start output buffering
        ob_start();
        
        // Display time donations if requested
        if ($type === 'hours' || $type === 'both') {
            // Get the time donations
            $donations = $this->db->get_donations_by_campaign($campaign_id);
            
            // Include the time donors template
            include WP_PETITION_PLUGIN_DIR . 'templates/donors-template.php';
        }
        
        // Display money donations if requested
        if ($type === 'money' || $type === 'both') {
            // Get the money donations (Stripe orders)
            $stripe_orders = $this->get_stripe_orders_by_campaign($campaign_id);
            
            // Include the money donors template
            include WP_PETITION_PLUGIN_DIR . 'templates/money-donors-template.php';
        }
        
        // Display Minutos donations if requested
        if ($type === 'minutos' || $type === 'both' || $type === 'time_minutos') {
            // Get the Minutos donations
            $minutos_donations = $this->get_minutos_donations_by_campaign($campaign_id);
            
            // Include the Minutos donors template
            include WP_PETITION_PLUGIN_DIR . 'templates/minutos-donors-template.php';
        }
        
        // Return the buffered content
        return ob_get_clean();
    }
    
    /**
     * Get Stripe orders for a campaign.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @param    bool     $include_test   Whether to include test mode orders.
     * @return   array                    The Stripe orders.
     */
    public function get_stripe_orders_by_campaign($campaign_id, $include_test = true) {
        // Check if Stripe integration is enabled in settings
        $stripe_integration = get_option('wp_petition_stripe_integration', 0);
        if (!$stripe_integration) {
            return array();
        }
        
        // Check if Stripe Payments plugin is active
        if (!post_type_exists('stripe_order')) {
            return array();
        }
        
        // Get the campaign
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return array();
        }
        
        // Get the associated Stripe product IDs
        $stripe_product_ids = get_post_meta($campaign_id, 'stripe_product_ids', true);
        if (empty($stripe_product_ids)) {
            return array();
        }
        
        // Convert to array if it's a string
        if (!is_array($stripe_product_ids)) {
            $stripe_product_ids = explode(',', $stripe_product_ids);
        }
        
        // Initialize orders array
        $orders = array();
        
        // Query Stripe orders (custom post type: stripe_order)
        $args = array(
            'post_type' => 'stripe_order',
            'post_status' => 'publish',
            'posts_per_page' => -1,
            'orderby' => 'date',
            'order' => 'DESC',
        );
        
        $stripe_orders = get_posts($args);
        
        foreach ($stripe_orders as $order) {
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
            $is_paid = false;
            
            if (empty($order_status)) {
                // Legacy orders might not have a status, check the charge data
                if (isset($order_data['charge']->paid) && $order_data['charge']->paid && 
                    isset($order_data['charge']->captured) && $order_data['charge']->captured) {
                    // Order is paid
                    $is_paid = true;
                }
            } else if ($order_status === 'paid') {
                // Order has a status and it's paid
                $is_paid = true;
            }
            
            if ($is_paid) {
                // Add to orders array
                $orders[] = array(
                    'order_id' => $order->ID,
                    'order_data' => $order_data,
                    'created_at' => strtotime($order->post_date),
                );
            }
        }
        
        return $orders;
    }

    /**
     * Generate a PDF export of the donors list.
     *
     * @since    1.0.0
     * @param    int      $campaign_id    The campaign ID.
     * @return   string                   The path to the generated PDF file.
     */
    public function generate_pdf_export($campaign_id) {
        // This is a placeholder function
        // In a real implementation, this would generate a PDF using a library like TCPDF or FPDF
        // For now, we'll just return a message
        return __('PDF export functionality would be implemented here.', 'wp-petition');
        
        // Example implementation with TCPDF:
        /*
        // Get the campaign
        $campaign = $this->db->get_campaign($campaign_id);
        if (!$campaign) {
            return false;
        }
        
        // Get the donations
        $donations = $this->db->get_donations_by_campaign($campaign_id);
        
        // Create new PDF document
        $pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        
        // Set document information
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('WP Petition');
        $pdf->SetTitle('Donors List - ' . $campaign->title);
        $pdf->SetSubject('Donors List');
        
        // Set default header data
        $pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, 'Donors List', $campaign->title);
        
        // Set header and footer fonts
        $pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
        $pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));
        
        // Set default monospaced font
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        
        // Set margins
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
        $pdf->SetFooterMargin(PDF_MARGIN_FOOTER);
        
        // Set auto page breaks
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        
        // Set image scale factor
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        
        // Add a page
        $pdf->AddPage();
        
        // Set font
        $pdf->SetFont('helvetica', '', 10);
        
        // Table header
        $html = '<table border="1" cellpadding="5">
            <tr>
                <th>Name</th>
                <th>Email</th>
                <th>Hours</th>
                <th>Facebook Post</th>
                <th>X Post</th>
                <th>Other Support</th>
                <th>Date</th>
            </tr>';
        
        // Table rows
        foreach ($donations as $donation) {
            $html .= '<tr>';
            $html .= '<td>' . $donation->name . '</td>';
            $html .= '<td>' . $donation->email . '</td>';
            $html .= '<td>' . $donation->hours . '</td>';
            $html .= '<td>' . ($donation->facebook_post ? 'Yes' : 'No') . '</td>';
            $html .= '<td>' . ($donation->x_post ? 'Yes' : 'No') . '</td>';
            $html .= '<td>' . $donation->other_support . '</td>';
            $html .= '<td>' . date('Y-m-d', strtotime($donation->created_at)) . '</td>';
            $html .= '</tr>';
        }
        
        $html .= '</table>';
        
        // Output the HTML content
        $pdf->writeHTML($html, true, false, true, false, '');
        
        // Close and output PDF document
        $file_path = WP_CONTENT_DIR . '/uploads/wp-petition/donors-' . $campaign_id . '.pdf';
        $pdf->Output($file_path, 'F');
        
        return $file_path;
        */
    }
}
