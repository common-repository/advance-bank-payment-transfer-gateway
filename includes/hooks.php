<?php

function abpt_add_gateways($gateways)
{
    $gateways[] = 'ABPT_Gateway_Advance_Bank_Payment_Offline';
    return $gateways;
}
add_filter('woocommerce_payment_gateways', 'abpt_add_gateways');


/**
 * Adds plugin page links
 */
function abpt_gateway_plugin_links($links)
{

    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout&section=offline_gateway') . '">' . __('Configure', 'wc-gateway-offline') . '</a>'
    );

    return array_merge($plugin_links, $links);
}
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'abpt_gateway_plugin_links');


function abpt_ajax_load_scripts()
{
    // load our jquery file that sends the $.post request
    wp_enqueue_script("common-ajax", plugin_dir_url(__FILE__) . '/js/common.js', array('jquery'));

    // make the ajaxurl var available to the above script
    wp_localize_script('common-ajax', 'the_ajax_script', array('ajaxurl' => admin_url('admin-ajax.php')));
}
add_action('wp_print_scripts', 'abpt_ajax_load_scripts');

function abpt_ajax_process_request()
{
    $wp_upload_dir = wp_upload_dir();
    $path = $wp_upload_dir['path'] . '/';

    $extension = pathinfo(sanitize_text_field($_FILES['file']['name']), PATHINFO_EXTENSION);
    $valid_formats = array("jpg", "png", "jpeg", "pdf"); // Supported file types

    if (!in_array(strtolower($extension), $valid_formats)) {
        return 0;
        die;
    } else {
        // Generate a new filename
        $new_filename = sanitize_file_name(uniqid() . '.' . $extension);
        
        // Update the filename in the $_FILES array before handling the upload
        $_FILES['file']['name'] = $new_filename;

        $upload_overrides = array('test_form' => false);
        $movefile = wp_handle_upload($_FILES['file'], $upload_overrides);  // wp_handle_upload handles sanitizing of $_FILES data.

        if ($movefile && !isset($movefile['error'])) {
            // File is uploaded successfully, get the filename and file type
            $filename = $movefile['file'];
            $filetype = wp_check_filetype(basename($filename), null);

            $attachment = array(
                'guid'           => $wp_upload_dir['url'] . '/' . basename($filename),
                'post_mime_type' => $filetype['type'],
                'post_title'     => preg_replace('/\.[^.]+$/', '', basename($filename)),
                'post_content'   => '',
                'post_status'    => 'inherit'
            );

            // Insert attachment to the database
            $attach_id = wp_insert_attachment($attachment, $filename, 0);

            // Uncomment if you need to generate and update attachment metadata
            // require_once(ABSPATH . 'wp-admin/includes/image.php');
            // $attach_data = wp_generate_attachment_metadata($attach_id, $filename);
            // wp_update_attachment_metadata($attach_id, $attach_data);

            echo $attach_id;
        } else {
            // Handle upload error
            echo $movefile['error'];
        }
        die;
    }
    die();
}
add_action('wp_ajax_invoice_response', 'abpt_ajax_process_request');
add_action( 'wp_ajax_nopriv_invoice_response', 'abpt_ajax_process_request' );
