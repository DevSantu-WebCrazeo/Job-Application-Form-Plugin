<?php
/**
 * Submission details template
 */

if (!defined('ABSPATH')) {
    exit;
}

if (!isset($submission) || !$submission) {
    echo '<p>Submission not found.</p>';
    return;
}
?>

<div class="jaf-submission-details">
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Application ID:</span>
        <span class="jaf-detail-value"><code><?php echo esc_html($submission->unique_id); ?></code></span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Name:</span>
        <span class="jaf-detail-value"><?php echo esc_html($submission->name); ?></span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Email:</span>
        <span class="jaf-detail-value">
            <a href="mailto:<?php echo esc_attr($submission->email); ?>">
                <?php echo esc_html($submission->email); ?>
            </a>
        </span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Phone:</span>
        <span class="jaf-detail-value">
            <a href="tel:<?php echo esc_attr($submission->phone); ?>">
                <?php echo esc_html($submission->phone); ?>
            </a>
        </span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Position:</span>
        <span class="jaf-detail-value"><?php echo esc_html($submission->position); ?></span>
    </div>
    
    <?php if (!empty($submission->message)): ?>
        <div class="jaf-detail-row">
            <span class="jaf-detail-label">Message:</span>
            <div class="jaf-detail-value">
                <?php echo nl2br(esc_html($submission->message)); ?>
            </div>
        </div>
    <?php endif; ?>
    
    <?php if (!empty($submission->resume_path)): ?>
        <div class="jaf-detail-row">
            <span class="jaf-detail-label">Resume:</span>
            <span class="jaf-detail-value">
                <?php 
                $download_url = JAF_File_Downloader::get_download_url($submission->id, $submission->unique_id);
                $filename = basename($submission->resume_path);
                ?>
                <a href="<?php echo esc_url($download_url); ?>" target="_blank" rel="noopener noreferrer">
                    <?php echo esc_html($filename); ?>
                </a>
            </span>
        </div>
    <?php endif; ?>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Page Title:</span>
        <span class="jaf-detail-value"><?php echo esc_html($submission->page_title); ?></span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Page URL:</span>
        <span class="jaf-detail-value">
            <a href="<?php echo esc_url($submission->page_url); ?>" target="_blank" rel="noopener noreferrer">
                <?php echo esc_html($submission->page_url); ?>
            </a>
        </span>
    </div>
    
    <div class="jaf-detail-row">
        <span class="jaf-detail-label">Submitted At:</span>
        <span class="jaf-detail-value">
            <?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'), strtotime($submission->submitted_at))); ?>
        </span>
    </div>
</div>

