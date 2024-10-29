<?php

/**
 * Plugin Name: Advance Bank Payment Transfer Gateway
 * Plugin URI: https://github.com/developer-ramesh
 * Description: Make Direct Payment in Bank Account and upload the bank payment receipt during checkout.
 * Author: Ramesh Kumar
 * Author URI: https://in.linkedin.com/in/developer-ramesh
 * Version: 6.4
 * Tested up to: 6.4.0
 * License: GNU General Public License v3.0
 */

defined('ABSPATH') or exit;


// Make sure WooCommerce is active
if (!in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) {
    return;
}

add_action('plugins_loaded', 'abpt_gateway_init', 11);

function abpt_gateway_init()
{
    require_once(plugin_basename('classes/wc_gateway_advance_bank_transfer.php'));
}

require_once(plugin_basename('includes/hooks.php'));
