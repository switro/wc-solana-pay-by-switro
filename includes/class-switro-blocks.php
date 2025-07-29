<?php

use Automattic\WooCommerce\Blocks\Payments\Integrations\AbstractPaymentMethodType;

final class WC_Switro_Blocks extends AbstractPaymentMethodType
{
    protected $name = 'switro_gateway';
    protected $gateway;

    public function initialize()
    {
        $gateways = WC()->payment_gateways()->payment_gateways();
        $this->gateway = $gateways[$this->name] ?? null;
    }

    public function is_active()
    {
        $is = $this->gateway && $this->gateway->is_available();

        return $is;
    }

    public function get_payment_method_script_handles()
    {

        wp_enqueue_script(
            'wc-switro-blocks-integration',
            plugins_url('block/switro-block.js', __DIR__),
            ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n'],
            '1.0.0',
            true
        );

        $settings = get_option('woocommerce_switro_gateway_settings', []);
        wp_add_inline_script(
            'wc-switro-blocks-integration',
            'window.wc = window.wc || {}; window.wc.wcSettings = window.wc.wcSettings || {}; window.wc.wcSettings["switro_gateway_data"] = ' . wp_json_encode([
                'title' => $settings['title'] ?? 'Switro Solana Wallet',
                'description' => $settings['description'] ?? 'Pay instantly with Switro Solana Wallet.',
                'ariaLabel' => $settings['title'] ?? 'Switro Solana Wallet',

            ]) . ';',
            'before'
        );

        return ['wc-switro-blocks-integration'];
    }

    public function get_payment_method_data()
    {
        $settings = get_option('woocommerce_switro_gateway_settings', []);
        return [
            'title' => $settings['title'] ?? __('Switro Solana Wallet', 'wc-solana-pay-by-switro'),
            'description' => $settings['description'] ?? __('Pay instantly with Switro Solana Wallet.', 'wc-solana-pay-by-switro'),
            'ariaLabel' => $settings['title'] ?? __('Switro Solana Wallet', 'wc-solana-pay-by-switro'),
            'supports' => ['products', 'default', 'virtual'],
        ];
    }

    public function enqueue_payment_method_script()
    {
        wp_enqueue_script(
            'wc-switro-blocks-integration',
            plugins_url('block/switro-block.js', __DIR__),
            ['wc-blocks-registry', 'wc-settings', 'wp-element', 'wp-i18n'],
            '1.0.0',
            true
        );
    }
}
