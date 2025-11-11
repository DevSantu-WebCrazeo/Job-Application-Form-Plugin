<?php
/**
 * Admin page template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>

<div class="wrap jaf-admin-wrap">
    <h1 class="wp-heading-inline">Job Applications</h1>
    
    <div class="jaf-admin-header">
        <div class="jaf-admin-actions">
            <a href="<?php echo esc_url(wp_nonce_url(admin_url('admin-post.php?action=jaf_export_submissions'), 'jaf_admin_nonce')); ?>" class="button button-secondary" id="jaf-export-btn">Export CSV</a>
        </div>
    </div>
    
    <div class="jaf-admin-filters">
        <form method="get" action="" class="jaf-filter-form">
            <input type="hidden" name="page" value="job-applications">
            
            <div class="jaf-filter-group">
                <label for="jaf-search">Search:</label>
                <input type="text" name="s" id="jaf-search" value="<?php echo esc_attr($search); ?>" placeholder="Search by name, email, phone, or ID">
            </div>
            
            <div class="jaf-filter-group">
                <label for="jaf-position">Position:</label>
                <select name="position" id="jaf-position">
                    <option value="">All Positions</option>
                    <?php foreach ($positions as $pos): ?>
                        <option value="<?php echo esc_attr($pos); ?>" <?php selected($position_filter, $pos); ?>>
                            <?php echo esc_html($pos); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="jaf-filter-group">
                <button type="submit" class="button">Filter</button>
                <?php if (!empty($search) || !empty($position_filter)): ?>
                    <a href="<?php echo esc_url(admin_url('admin.php?page=job-applications')); ?>" class="button">Clear</a>
                <?php endif; ?>
            </div>
        </form>
    </div>
    
    <div class="jaf-admin-stats">
        <p>Total Applications: <strong><?php echo number_format($total); ?></strong></p>
    </div>
    
    <?php if (empty($submissions)): ?>
        <div class="jaf-no-results">
            <p>No applications found.</p>
        </div>
    <?php else: ?>
        <table class="wp-list-table widefat fixed striped jaf-submissions-table">
            <thead>
                <tr>
                    <th class="column-id">ID</th>
                    <th class="column-unique-id">Unique ID</th>
                    <th class="column-name">Name</th>
                    <th class="column-email">Email</th>
                    <th class="column-phone">Phone</th>
                    <th class="column-position">Position</th>
                    <th class="column-page">Page</th>
                    <th class="column-date">Submitted</th>
                    <th class="column-actions">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($submissions as $submission): ?>
                    <tr data-id="<?php echo esc_attr($submission->id); ?>">
                        <td class="column-id"><?php echo esc_html($submission->id); ?></td>
                        <td class="column-unique-id">
                            <code><?php echo esc_html($submission->unique_id); ?></code>
                        </td>
                        <td class="column-name">
                            <strong><?php echo esc_html($submission->name); ?></strong>
                        </td>
                        <td class="column-email">
                            <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                                <?php echo esc_html($submission->email); ?>
                            </a>
                        </td>
                        <td class="column-phone">
                            <a href="tel:<?php echo esc_attr($submission->phone); ?>">
                                <?php echo esc_html($submission->phone); ?>
                            </a>
                        </td>
                        <td class="column-position"><?php echo esc_html($submission->position); ?></td>
                        <td class="column-page">
                            <a href="<?php echo esc_url($submission->page_url); ?>" target="_blank" title="<?php echo esc_attr($submission->page_title); ?>">
                                <?php echo esc_html(wp_trim_words($submission->page_title, 5)); ?>
                            </a>
                        </td>
                        <td class="column-date">
                            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->submitted_at))); ?>
                        </td>
                        <td class="column-actions">
                            <button type="button" class="button button-small jaf-view-btn" data-id="<?php echo esc_attr($submission->id); ?>">View</button>
                            <?php if (!empty($submission->resume_path)): ?>
                                <?php 
                                $download_url = JAF_File_Downloader::get_download_url($submission->id, $submission->unique_id);
                                ?>
                                <a href="<?php echo esc_url($download_url); ?>" class="button button-small" target="_blank" title="Download Resume">Download</a>
                            <?php endif; ?>
                            <button type="button" class="button button-small button-link-delete jaf-delete-btn" data-id="<?php echo esc_attr($submission->id); ?>">Delete</button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        
        <?php if ($total_pages > 1): ?>
            <div class="jaf-pagination">
                <?php
                $pagination_args = array(
                    'base' => add_query_arg('paged', '%#%'),
                    'format' => '',
                    'prev_text' => '&laquo;',
                    'next_text' => '&raquo;',
                    'total' => $total_pages,
                    'current' => $current_page
                );
                
                if (!empty($search)) {
                    $pagination_args['add_args'] = array('s' => $search);
                }
                
                if (!empty($position_filter)) {
                    $pagination_args['add_args']['position'] = $position_filter;
                }
                
                echo paginate_links($pagination_args);
                ?>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<!-- View Modal -->
<div id="jaf-view-modal" class="jaf-modal" style="display: none;">
    <div class="jaf-modal-content">
        <div class="jaf-modal-header">
            <h2>Application Details</h2>
            <span class="jaf-modal-close">&times;</span>
        </div>
        <div class="jaf-modal-body" id="jaf-modal-body">
            <!-- Content will be loaded via AJAX -->
        </div>
    </div>
</div>

