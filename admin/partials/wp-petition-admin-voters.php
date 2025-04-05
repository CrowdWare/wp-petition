<?php
/**
 * Provide a admin area view for the voters list
 *
 * This file is used to markup the admin-facing aspects of the plugin.
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

<div class="wrap">
    <h1><?php echo esc_html__('Voters', 'wp-petition'); ?></h1>
    
    <!-- Campaign Filter -->
    <div class="tablenav top">
        <div class="alignleft actions">
            <form method="get">
                <input type="hidden" name="page" value="wp-petition-voters">
                <select name="campaign_id">
                    <option value="0"><?php echo esc_html__('All Campaigns', 'wp-petition'); ?></option>
                    <?php foreach ($campaigns as $campaign) : ?>
                        <option value="<?php echo esc_attr($campaign->campaign_id); ?>" <?php selected($campaign_id, $campaign->campaign_id); ?>>
                            <?php echo esc_html($campaign->title); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <input type="submit" class="button" value="<?php echo esc_attr__('Filter', 'wp-petition'); ?>">
            </form>
        </div>
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num">
                    <?php printf(
                        esc_html__('%s items', 'wp-petition'),
                        number_format_i18n($total_votes)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    // First page link
                    if ($page > 1) {
                        printf(
                            '<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => 1, 'campaign_id' => $campaign_id))),
                            esc_html__('First page', 'wp-petition'),
                            '&laquo;'
                        );
                    } else {
                        printf(
                            '<span class="first-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('First page', 'wp-petition'),
                            '&laquo;'
                        );
                    }
                    
                    // Previous page link
                    if ($page > 1) {
                        printf(
                            '<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => max(1, $page - 1), 'campaign_id' => $campaign_id))),
                            esc_html__('Previous page', 'wp-petition'),
                            '&lsaquo;'
                        );
                    } else {
                        printf(
                            '<span class="prev-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Previous page', 'wp-petition'),
                            '&lsaquo;'
                        );
                    }
                    
                    // Current page info
                    printf(
                        '<span class="paging-input"><span class="tablenav-paging-text">%s %s %s</span></span>',
                        number_format_i18n($page),
                        esc_html__('of', 'wp-petition'),
                        number_format_i18n($total_pages)
                    );
                    
                    // Next page link
                    if ($page < $total_pages) {
                        printf(
                            '<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => min($total_pages, $page + 1), 'campaign_id' => $campaign_id))),
                            esc_html__('Next page', 'wp-petition'),
                            '&rsaquo;'
                        );
                    } else {
                        printf(
                            '<span class="next-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Next page', 'wp-petition'),
                            '&rsaquo;'
                        );
                    }
                    
                    // Last page link
                    if ($page < $total_pages) {
                        printf(
                            '<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => $total_pages, 'campaign_id' => $campaign_id))),
                            esc_html__('Last page', 'wp-petition'),
                            '&raquo;'
                        );
                    } else {
                        printf(
                            '<span class="last-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Last page', 'wp-petition'),
                            '&raquo;'
                        );
                    }
                    ?>
                </span>
            <?php endif; ?>
        </div>
        <br class="clear">
    </div>
    
    <!-- Voters Table -->
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-campaign"><?php echo esc_html__('Campaign', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-name"><?php echo esc_html__('Name', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-email"><?php echo esc_html__('Email', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-interest"><?php echo esc_html__('Interest', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-role"><?php echo esc_html__('Role', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-notes"><?php echo esc_html__('Notes', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-admin-notes"><?php echo esc_html__('Admin Notes', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-date"><?php echo esc_html__('Date', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php echo esc_html__('Actions', 'wp-petition'); ?></th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($votes)) : ?>
                <tr>
                    <td colspan="9"><?php echo esc_html__('No voters found.', 'wp-petition'); ?></td>
                </tr>
            <?php else : ?>
                <?php foreach ($votes as $vote) : ?>
                    <tr id="vote-<?php echo esc_attr($vote->votes_id); ?>">
                        <td><?php echo esc_html($vote->votes_id); ?></td>
                        <td><?php echo esc_html($vote->campaign_title); ?></td>
                        <td><?php echo esc_html($vote->name); ?></td>
                        <td><?php echo esc_html($vote->email); ?></td>
                        <td><?php echo $vote->interest ? esc_html__('Yes', 'wp-petition') : esc_html__('No', 'wp-petition'); ?></td>
                        <td><?php echo esc_html($vote->contribution_role); ?></td>
                        <td class="notes-cell">
                            <div class="notes-display"><?php echo esc_html($vote->notes); ?></div>
                        </td>
                        <td class="admin-notes-cell">
                            <div class="admin-notes-display"><?php echo esc_html($vote->admin_notes); ?></div>
                            <div class="admin-notes-edit" style="display: none;">
                                <textarea class="admin-notes-textarea" rows="3"><?php echo esc_textarea($vote->admin_notes); ?></textarea>
                            </div>
                        </td>
                        <td><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($vote->created_at))); ?></td>
                        <td>
                            <div class="action-buttons">
                                <button type="button" class="button edit-admin-notes" data-vote-id="<?php echo esc_attr($vote->votes_id); ?>">
                                    <?php echo esc_html__('Edit Admin Notes', 'wp-petition'); ?>
                                </button>
                            </div>
                            <div class="save-cancel-admin-buttons" style="display: none;">
                                <button type="button" class="button button-primary save-admin-notes" data-vote-id="<?php echo esc_attr($vote->votes_id); ?>">
                                    <?php echo esc_html__('Save', 'wp-petition'); ?>
                                </button>
                                <button type="button" class="button cancel-admin-edit">
                                    <?php echo esc_html__('Cancel', 'wp-petition'); ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
        <tfoot>
            <tr>
                <th scope="col" class="manage-column column-id"><?php echo esc_html__('ID', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-campaign"><?php echo esc_html__('Campaign', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-name"><?php echo esc_html__('Name', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-email"><?php echo esc_html__('Email', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-interest"><?php echo esc_html__('Interest', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-role"><?php echo esc_html__('Role', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-notes"><?php echo esc_html__('Notes', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-admin-notes"><?php echo esc_html__('Admin Notes', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-date"><?php echo esc_html__('Date', 'wp-petition'); ?></th>
                <th scope="col" class="manage-column column-actions"><?php echo esc_html__('Actions', 'wp-petition'); ?></th>
            </tr>
        </tfoot>
    </table>
    
    <!-- Bottom Pagination -->
    <div class="tablenav bottom">
        <div class="tablenav-pages">
            <?php if ($total_pages > 1) : ?>
                <span class="displaying-num">
                    <?php printf(
                        esc_html__('%s items', 'wp-petition'),
                        number_format_i18n($total_votes)
                    ); ?>
                </span>
                <span class="pagination-links">
                    <?php
                    // First page link
                    if ($page > 1) {
                        printf(
                            '<a class="first-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => 1, 'campaign_id' => $campaign_id))),
                            esc_html__('First page', 'wp-petition'),
                            '&laquo;'
                        );
                    } else {
                        printf(
                            '<span class="first-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('First page', 'wp-petition'),
                            '&laquo;'
                        );
                    }
                    
                    // Previous page link
                    if ($page > 1) {
                        printf(
                            '<a class="prev-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => max(1, $page - 1), 'campaign_id' => $campaign_id))),
                            esc_html__('Previous page', 'wp-petition'),
                            '&lsaquo;'
                        );
                    } else {
                        printf(
                            '<span class="prev-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Previous page', 'wp-petition'),
                            '&lsaquo;'
                        );
                    }
                    
                    // Current page info
                    printf(
                        '<span class="paging-input"><span class="tablenav-paging-text">%s %s %s</span></span>',
                        number_format_i18n($page),
                        esc_html__('of', 'wp-petition'),
                        number_format_i18n($total_pages)
                    );
                    
                    // Next page link
                    if ($page < $total_pages) {
                        printf(
                            '<a class="next-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => min($total_pages, $page + 1), 'campaign_id' => $campaign_id))),
                            esc_html__('Next page', 'wp-petition'),
                            '&rsaquo;'
                        );
                    } else {
                        printf(
                            '<span class="next-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Next page', 'wp-petition'),
                            '&rsaquo;'
                        );
                    }
                    
                    // Last page link
                    if ($page < $total_pages) {
                        printf(
                            '<a class="last-page button" href="%s"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></a>',
                            esc_url(add_query_arg(array('paged' => $total_pages, 'campaign_id' => $campaign_id))),
                            esc_html__('Last page', 'wp-petition'),
                            '&raquo;'
                        );
                    } else {
                        printf(
                            '<span class="last-page button disabled"><span class="screen-reader-text">%s</span><span aria-hidden="true">%s</span></span>',
                            esc_html__('Last page', 'wp-petition'),
                            '&raquo;'
                        );
                    }
                    ?>
                </span>
            <?php endif; ?>
        </div>
        <br class="clear">
    </div>
</div>

<script type="text/javascript">
jQuery(document).ready(function($) {
    // Edit admin notes button click
    $('.edit-admin-notes').on('click', function() {
        var row = $(this).closest('tr');
        row.find('.admin-notes-display').hide();
        row.find('.admin-notes-edit').show();
        row.find('.action-buttons').hide();
        row.find('.save-cancel-admin-buttons').show();
    });
    
    // Cancel admin edit button click
    $('.cancel-admin-edit').on('click', function() {
        var row = $(this).closest('tr');
        row.find('.admin-notes-edit').hide();
        row.find('.admin-notes-display').show();
        row.find('.save-cancel-admin-buttons').hide();
        row.find('.action-buttons').show();
    });
    
    // Save admin notes button click
    $('.save-admin-notes').on('click', function() {
        var row = $(this).closest('tr');
        var vote_id = $(this).data('vote-id');
        var admin_notes = row.find('.admin-notes-textarea').val();
        var saveButton = $(this);
        
        // Disable the save button while saving
        saveButton.prop('disabled', true).text('<?php echo esc_js(__('Saving...', 'wp-petition')); ?>');
        
        // Send AJAX request
        $.ajax({
            url: wp_petition_admin.ajax_url,
            type: 'POST',
            data: {
                action: 'wp_petition_update_vote_admin_notes',
                vote_id: vote_id,
                admin_notes: admin_notes,
                nonce: wp_petition_admin.nonce
            },
            success: function(response) {
                if (response.success) {
                    // Update the displayed admin notes
                    row.find('.admin-notes-display').text(admin_notes).show();
                    row.find('.admin-notes-edit').hide();
                    row.find('.save-cancel-admin-buttons').hide();
                    row.find('.action-buttons').show();
                    
                    // Show success message
                    alert(response.data.message);
                } else {
                    // Show error message
                    alert(response.data.message);
                }
            },
            error: function() {
                alert('<?php echo esc_js(__('An error occurred while saving the admin notes.', 'wp-petition')); ?>');
            },
            complete: function() {
                // Re-enable the save button
                saveButton.prop('disabled', false).text('<?php echo esc_js(__('Save', 'wp-petition')); ?>');
            }
        });
    });
});
</script>

<style type="text/css">
.notes-textarea, .admin-notes-textarea {
    width: 100%;
    min-width: 150px;
}
.column-notes, .column-admin-notes {
    width: 15%;
}
.column-actions {
    width: 200px;
}
.save-cancel-buttons, .save-cancel-admin-buttons {
    display: flex;
    gap: 5px;
    margin-top: 5px;
}
.action-buttons {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
</style>
