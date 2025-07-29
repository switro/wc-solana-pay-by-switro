<?php

/**
 * Plugin Name: WC Solana Pay By Switro
 * Description: Accept Solana payments instantly on your WooCommerce store with Switro – a modern, non-custodial crypto payment gateway.
 * Author: Switro
 * Author URI: https://switro.com/
 * Text Domain: wc-solana-pay-by-switro
 * WC requires at least: 7.6
 * WC tested up to: 10.0.0
 * Version: 1.0.0
 * Requires PHP: 7.4
 * Tested up to: 6.8
 * License: GPL-2.0+
 * License URI: http://www.gnu.org/licenses/gpl-2.0.txt
 * Requires Plugins: woocommerce
 */

if (!defined('ABSPATH')) exit;

if (! in_array('woocommerce/woocommerce.php', apply_filters('active_plugins', get_option('active_plugins')))) return;

add_action('before_woocommerce_init', function () {
    if (class_exists('\Automattic\WooCommerce\Utilities\FeaturesUtil')) {
        // Enable block checkout + HPOS compatibility
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'cart_checkout_blocks',
            __FILE__,
            true
        );
        \Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility(
            'custom_order_tables',
            __FILE__,
            true
        );
    }
});

add_action('plugins_loaded', 'switro_init_gateway_class', 11);
function switro_init_gateway_class()
{
    if (class_exists('WC_Payment_Gateway')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-wc-gateway-switro.php';
        require_once plugin_dir_path(__FILE__) . 'includes/class-switro-blocks.php';

        require_once plugin_dir_path(__FILE__) . 'includes/class-switro-handler.php';
        new WC_Gateway_Switro_Handler();
    }
}

add_filter('woocommerce_payment_gateways', 'switro_add_gateway_class');
function switro_add_gateway_class($methods)
{
    $methods[] = 'WC_Gateway_Switro';
    return $methods;
}

// Block support
add_action('woocommerce_blocks_loaded', function () {
    if (!class_exists('WC_Switro_Blocks')) {
        require_once plugin_dir_path(__FILE__) . 'includes/class-switro-blocks.php';
    }

    add_action('woocommerce_blocks_payment_method_type_registration', function ($registry) {
        if (
            class_exists('\\Automattic\\WooCommerce\\Blocks\\Payments\\PaymentMethodRegistry') &&
            class_exists('\\Automattic\\WooCommerce\\Blocks\\Payments\\Integrations\\AbstractPaymentMethodType')
        ) {
            $registry->register(new WC_Switro_Blocks());
        }
    });
});

add_action('admin_footer', function () {
    $screen = get_current_screen();
    if (strpos($screen->id, 'woocommerce') === false) {
        return;
    }
?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btn = document.getElementById('copy-webhook-btn');
            const input = document.querySelector('input.switro-webhook-url');

            if (btn && input) {
                btn.addEventListener('click', function() {
                    input.select();
                    input.setSelectionRange(0, 99999); // mobile

                    try {
                        document.execCommand('copy');
                        btn.textContent = 'Copied!';
                        btn.disabled = true;
                        setTimeout(() => {
                            btn.textContent = 'Copy to Clipboard';
                            btn.disabled = false;
                        }, 2000);
                    } catch (err) {
                        console.error('Copy failed:', err);
                        btn.textContent = 'Failed to Copy';
                    }
                });
            }
        });
    </script>
<?php
});
