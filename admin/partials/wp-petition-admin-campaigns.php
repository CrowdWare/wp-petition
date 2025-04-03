<?php
/**
 * Admin campaigns page template.
 *
 * @link       https://example.com
 * @since      1.0.0
 *
 * @package    WP_Petition
 * @subpackage WP_Petition/admin/partials
 */

// If this file is called directly, abort.
if (!defined('WPINC')) {
    die;
}
?>

<div class="wrap wp-petition-admin-wrap">
    <div class="wp-petition-admin-header">
        <h1><?php echo esc_html__('Campaigns', 'wp-petition'); ?></h1>
        <a href="<?php echo esc_url(admin_url('admin.php?page=wp-petition-add-campaign')); ?>" class="button button-primary add-new-campaign"><?php echo esc_html__('Add New Campaign', 'wp-petition'); ?></a>
    </div>
    
    <div class="wp-petition-admin-notices"></div>
    
    <?php if (empty($campaigns)) : ?>
        <p><?php echo esc_html__('No campaigns found. Click the "Add New Campaign" button to create your first campaign.', 'wp-petition'); ?></p>
    <?php else : ?>
        <table class="wp-petition-campaigns-table">
            <thead>
                <tr>
                    <th><?php echo esc_html__('ID', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Title', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Hours Goal', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Hours Donated', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Minutos Goal', 'wp-petition'); ?></th>
<th><?php echo esc_html__('Votes Goal', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Minutos Donated', 'wp-petition'); ?></th>
<th><?php echo esc_html__('Votes Donated', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Money Goal', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Money Donated', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Page', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Created', 'wp-petition'); ?></th>
                    <th><?php echo esc_html__('Actions', 'wp-petition'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($campaigns as $campaign) : 
                    $stats = $this->campaign->get_donation_stats($campaign->campaign_id);
                    $page_title = $campaign->page_id ? get_the_title($campaign->page_id) : '-';
                ?>
                    <tr id="campaign-row-<?php echo esc_attr($campaign->campaign_id); ?>">
                        <td><?php echo esc_html($campaign->campaign_id); ?></td>
                        <td><?php echo esc_html($campaign->title); ?></td>
                        <td><?php echo esc_html($campaign->goal_hours); ?></td>
                        <td><?php echo esc_html($stats['total_hours']); ?> (<?php echo esc_html(round($stats['hours_percentage'], 1)); ?>%)</td>
                        <td><?php echo esc_html(isset($campaign->goal_minutos) ? $campaign->goal_minutos : 0); ?></td>
<td><?php echo esc_html(isset($campaign->goal_votes) ? $campaign->goal_votes : 0); ?></td>
<td><?php 
    $minutos_stats = $this->campaign->get_minutos_stats($campaign->campaign_id);
    echo esc_html($minutos_stats['total_minutos']); 
    if (isset($campaign->goal_minutos) && $campaign->goal_minutos > 0) {
        echo ' (' . esc_html(round($minutos_stats['minutos_percentage'], 1)) . '%)';
    }
?></td>
<td><?php 
    $votes_stats = $this->campaign->get_votes_stats($campaign->campaign_id);
    echo esc_html($votes_stats['total_votes']); 
    if (isset($campaign->goal_votes) && $campaign->goal_votes > 0) {
        echo ' (' . esc_html(round($votes_stats['votes_percentage'], 1)) . '%)';
    }
?></td>
                        <td><?php echo esc_html(number_format($campaign->goal_amount, 2)); ?> €</td>
                        <td><?php echo esc_html(number_format($stats['total_amount'], 2)); ?> € (<?php echo esc_html(round($stats['amount_percentage'], 1)); ?>%)</td>
                        <td><?php echo esc_html($page_title); ?></td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format'), strtotime($campaign->created_at))); ?></td>
                        <td class="actions">
                            <a href="<?php echo esc_url(admin_url('admin.php?page=wp-petition-add-campaign&campaign_id=' . $campaign->campaign_id)); ?>" class="button button-small"><?php echo esc_html__('Edit', 'wp-petition'); ?></a>
                            <a href="#" class="button button-small wp-petition-export-donors" data-campaign-id="<?php echo esc_attr($campaign->campaign_id); ?>"><?php echo esc_html__('Export', 'wp-petition'); ?></a>
                            <a href="#" class="button button-small button-link-delete wp-petition-delete-campaign" data-campaign-id="<?php echo esc_attr($campaign->campaign_id); ?>" data-campaign-title="<?php echo esc_attr($campaign->title); ?>"><?php echo esc_html__('Delete', 'wp-petition'); ?></a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
    
    <div class="wp-petition-shortcodes-info">
        <h3><?php echo esc_html__('Shortcodes', 'wp-petition'); ?></h3>
        <p><?php echo esc_html__('Use the following shortcodes to display campaign elements on your pages:', 'wp-petition'); ?></p>
        <ul>
            <li><code>[petition_form id=X]</code> - <?php echo esc_html__('Displays the donation form for the campaign with ID X.', 'wp-petition'); ?></li>
            <li><code>[petition_donors id=X]</code> - <?php echo esc_html__('Displays the donors list for the campaign with ID X.', 'wp-petition'); ?></li>
            <li><code>[petition_progress id=X type=hours|money|minutos display=bar|text]</code> - <?php echo esc_html__('Displays the progress bar or text for the campaign with ID X. The type parameter can be "hours", "money", or "minutos". The display parameter can be "bar" or "text".', 'wp-petition'); ?></li>
        </ul>
    </div>
</div>
