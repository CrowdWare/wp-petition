<?php
/**
 * Admin add/edit campaign page template.
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

// Check if we're editing an existing campaign
$is_edit = isset($campaign) && $campaign;
$campaign_id = $is_edit ? $campaign->campaign_id : 0;
$title = $is_edit ? $campaign->title : '';
$description = $is_edit ? $campaign->description : '';
$goal_hours = $is_edit ? $campaign->goal_hours : 0;
$goal_amount = $is_edit ? $campaign->goal_amount : 0.00;
$goal_minutos = $is_edit && isset($campaign->goal_minutos) ? $campaign->goal_minutos : 0;
$goal_votes = $is_edit && isset($campaign->goal_votes) ? $campaign->goal_votes : 0; // Define goal_votes variable
$start_date = $is_edit && $campaign->start_date ? date('Y-m-d', strtotime($campaign->start_date)) : '';
$end_date = $is_edit && $campaign->end_date ? date('Y-m-d', strtotime($campaign->end_date)) : '';
$page_id = $is_edit ? $campaign->page_id : 0;

// Get all pages for the dropdown
$pages = get_pages();
?>

<div class="wrap wp-petition-admin-wrap">
    <div class="wp-petition-admin-header">
        <h1><?php echo $is_edit ? esc_html__('Edit Campaign', 'wp-petition') : esc_html__('Add New Campaign', 'wp-petition'); ?></h1>
    </div>
    
    <div class="wp-petition-admin-notices"></div>
    
    <form id="wp-petition-campaign-form" class="wp-petition-campaign-form">
        <?php wp_nonce_field('wp_petition_campaign_form', 'wp_petition_campaign_nonce'); ?>
        <input type="hidden" name="campaign_id" value="<?php echo esc_attr($campaign_id); ?>">
        
        <div class="form-field">
            <label for="title"><?php echo esc_html__('Title', 'wp-petition'); ?> *</label>
            <input type="text" name="title" id="title" value="<?php echo esc_attr($title); ?>" required>
        </div>
        
        <div class="form-field">
            <label for="description"><?php echo esc_html__('Description', 'wp-petition'); ?></label>
            <textarea name="description" id="description" rows="5"><?php echo esc_textarea($description); ?></textarea>
        </div>
        
        <div class="form-field">
            <label for="goal_hours"><?php echo esc_html__('Hours Goal', 'wp-petition'); ?></label>
            <input type="number" name="goal_hours" id="goal_hours" min="0" value="<?php echo esc_attr($goal_hours); ?>">
        </div>
        
        <div class="form-field">
            <label for="goal_amount"><?php echo esc_html__('Money Goal (€)', 'wp-petition'); ?></label>
            <input type="number" name="goal_amount" id="goal_amount" min="0" step="0.01" value="<?php echo esc_attr($goal_amount); ?>">
        </div>
        
        <div class="form-field">
            <label for="goal_minutos"><?php echo esc_html__('Minutos Goal', 'wp-petition'); ?></label>
            <input type="number" name="goal_minutos" id="goal_minutos" min="0" value="<?php echo esc_attr($goal_minutos); ?>">
        </div>

        <div class="form-field">
            <label for="goal_votes"><?php echo esc_html__('Votes Goal', 'wp-petition'); ?></label>
            <input type="number" name="goal_votes" id="goal_votes" min="0" value="<?php echo esc_attr($goal_votes); ?>">
        </div>
        
        <div class="form-field">
            <label for="start_date"><?php echo esc_html__('Start Date', 'wp-petition'); ?></label>
            <input type="date" name="start_date" id="start_date" class="wp-petition-datepicker" value="<?php echo esc_attr($start_date); ?>">
        </div>
        
        <div class="form-field">
            <label for="end_date"><?php echo esc_html__('End Date', 'wp-petition'); ?></label>
            <input type="date" name="end_date" id="end_date" class="wp-petition-datepicker" value="<?php echo esc_attr($end_date); ?>">
        </div>
        
        <div class="form-field">
            <label for="page_id"><?php echo esc_html__('Associated Page', 'wp-petition'); ?></label>
            <select name="page_id" id="page_id" class="wp-petition-page-select">
                <option value=""><?php echo esc_html__('-- Select Page --', 'wp-petition'); ?></option>
                <?php foreach ($pages as $page) : ?>
                    <option value="<?php echo esc_attr($page->ID); ?>" <?php selected($page_id, $page->ID); ?>><?php echo esc_html($page->post_title); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="description"><?php echo esc_html__('Select the page where this campaign will be displayed.', 'wp-petition'); ?></p>
        </div>
        
        <div class="form-field submit-button">
            <button type="submit" class="button button-primary">
                <?php echo $is_edit ? esc_html__('Update Campaign', 'wp-petition') : esc_html__('Create Campaign', 'wp-petition'); ?>
            </button>
        </div>
    </form>
    
    <?php if ($is_edit) : ?>
        <div class="wp-petition-shortcodes-info">
            <h3><?php echo esc_html__('Shortcodes for this Campaign', 'wp-petition'); ?></h3>
            <p><?php echo esc_html__('Use the following shortcodes to display this campaign on your pages:', 'wp-petition'); ?></p>
            <ul>
                <li><code>[petition_form id=<?php echo esc_html($campaign_id); ?>]</code> - <?php echo esc_html__('Displays the donation form.', 'wp-petition'); ?></li>
                <li><code>[petition_donors id=<?php echo esc_html($campaign_id); ?> type=time]</code> - <?php echo esc_html__('Displays the time donors list.', 'wp-petition'); ?></li>
                <li><code>[petition_donors id=<?php echo esc_html($campaign_id); ?> type=money]</code> - <?php echo esc_html__('Displays the money donors list.', 'wp-petition'); ?></li>
                <li><code>[petition_donors id=<?php echo esc_html($campaign_id); ?> type=both]</code> - <?php echo esc_html__('Displays both time and money donors lists.', 'wp-petition'); ?></li>
                <li><code>[petition_progress id=<?php echo esc_html($campaign_id); ?> type=hours display=bar]</code> - <?php echo esc_html__('Displays the hours progress bar.', 'wp-petition'); ?></li>
                <li><code>[petition_progress id=<?php echo esc_html($campaign_id); ?> type=money display=bar]</code> - <?php echo esc_html__('Displays the money progress bar.', 'wp-petition'); ?></li>
                <li><code>[petition_progress id=<?php echo esc_html($campaign_id); ?> type=minutos display=bar]</code> - <?php echo esc_html__('Displays the Minutos progress bar.', 'wp-petition'); ?></li>
            </ul>
        </div>
    <?php endif; ?>
</div>
