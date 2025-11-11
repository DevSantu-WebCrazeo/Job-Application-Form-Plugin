<?php
/**
 * Admin notification email template
 */

if (!defined('ABSPATH')) {
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>New Job Application</title>
</head>
<body style="margin: 0; padding: 0; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; background-color: #f4f4f4;">
    <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="background-color: #f4f4f4;">
        <tr>
            <td style="padding: 40px 20px;">
                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%" style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                    <!-- Header -->
                    <tr>
                        <td style="background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%); padding: 30px; text-align: center; border-radius: 8px 8px 0 0;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 28px; font-weight: 700;">New Job Application</h1>
                        </td>
                    </tr>
                    
                    <!-- Content -->
                    <tr>
                        <td style="padding: 30px;">
                            <p style="margin: 0 0 20px 0; color: #374151; font-size: 16px; line-height: 1.6;">
                                A new job application has been submitted through your website.
                            </p>
                            
                            <div style="background-color: #f9fafb; border-left: 4px solid #2563eb; padding: 20px; margin: 20px 0; border-radius: 4px;">
                                <h2 style="margin: 0 0 15px 0; color: #1f2937; font-size: 20px; font-weight: 600;">Application Details</h2>
                                
                                <table role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%">
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; width: 140px; font-weight: 600;">Application ID:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <code style="background-color: #e5e7eb; padding: 4px 8px; border-radius: 4px; font-family: 'Courier New', monospace; color: #2563eb; font-weight: 600;"><?php echo esc_html($data['unique_id']); ?></code>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Name:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px; font-weight: 500;"><?php echo esc_html($data['name']); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Email:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <a href="mailto:<?php echo esc_attr($data['email']); ?>" style="color: #2563eb; text-decoration: none;"><?php echo esc_html($data['email']); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Phone:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <a href="tel:<?php echo esc_attr($data['phone']); ?>" style="color: #2563eb; text-decoration: none;"><?php echo esc_html($data['phone']); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Position:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px; font-weight: 500;"><?php echo esc_html($data['position']); ?></td>
                                    </tr>
                                    <?php if (!empty($data['message'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600; vertical-align: top;">Message:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px; line-height: 1.6;">
                                            <?php echo nl2br(esc_html($data['message'])); ?>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <?php if (!empty($data['resume_path'])): ?>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Resume:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <?php 
                                            $download_url = JAF_File_Downloader::get_download_url(0, $data['unique_id']);
                                            ?>
                                            <a href="<?php echo esc_url($download_url); ?>" style="color: #2563eb; text-decoration: none; font-weight: 500;">Download Resume</a>
                                        </td>
                                    </tr>
                                    <?php endif; ?>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Page Title:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;"><?php echo esc_html($data['page_title']); ?></td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Page URL:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;">
                                            <a href="<?php echo esc_url($data['page_url']); ?>" style="color: #2563eb; text-decoration: none; word-break: break-all;"><?php echo esc_html($data['page_url']); ?></a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td style="padding: 8px 0; color: #6b7280; font-size: 14px; font-weight: 600;">Submitted At:</td>
                                        <td style="padding: 8px 0; color: #1f2937; font-size: 14px;"><?php echo esc_html(date_i18n(get_option('date_format') . ' ' . get_option('time_format'))); ?></td>
                                    </tr>
                                </table>
                            </div>
                            
                            <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid #e5e7eb;">
                                <a href="<?php echo esc_url(admin_url('admin.php?page=job-applications')); ?>" style="display: inline-block; background-color: #2563eb; color: #ffffff; text-decoration: none; padding: 12px 24px; border-radius: 6px; font-weight: 600; font-size: 14px;">View in Admin Panel</a>
                            </div>
                        </td>
                    </tr>
                    
                    <!-- Footer -->
                    <tr>
                        <td style="background-color: #f9fafb; padding: 20px 30px; text-align: center; border-radius: 0 0 8px 8px; border-top: 1px solid #e5e7eb;">
                            <p style="margin: 0; color: #6b7280; font-size: 12px; line-height: 1.5;">
                                This is an automated notification from <?php echo esc_html(get_bloginfo('name')); ?><br>
                                <a href="<?php echo esc_url(home_url()); ?>" style="color: #2563eb; text-decoration: none;"><?php echo esc_html(home_url()); ?></a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>

