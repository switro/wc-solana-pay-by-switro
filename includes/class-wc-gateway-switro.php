<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Switro extends WC_Payment_Gateway
{

    public $id;
    public $method_title;
    public $method_description;
    public $has_fields;
    public $supports;
    public $title;
    public $description;
    public $enabled;
    public $api_key;
    public $cancel_url;
    public $success_url;
    public $network;

    public function __construct()
    {
        $this->id = 'switro_gateway';
        $this->method_title = __('Switro Solana Wallet', 'wc-solana-pay-by-switro');
        $this->method_description = __('Accept Solana payments with Switro.', 'wc-solana-pay-by-switro');
        $this->has_fields = false;
        $this->supports = ['products', 'default', 'virtual'];

        // Load settings
        $this->init_form_fields();
        $this->init_settings();

        $this->title = $this->get_option('title');
        $this->description = $this->get_option('description');
        $this->enabled = $this->get_option('enabled');
        $this->api_key = $this->get_option('api_key');
        $this->network = $this->get_option('network');
        $this->cancel_url  = $this->get_option('cancel_url');
        $this->success_url = $this->get_option('success_url');

        add_action('woocommerce_update_options_payment_gateways_' . $this->id, [$this, 'process_admin_options']);
    }

    public function init_form_fields()
    {
        $this->form_fields = [
            'enabled' => [
                'title'   => __('Enable/Disable', 'wc-solana-pay-by-switro'),
                'type'    => 'checkbox',
                'label'   => __('Enable Switro Payment Gateway', 'wc-solana-pay-by-switro'),
                'default' => 'no'
            ],
            'title' => [
                'title'       => __('Title', 'wc-solana-pay-by-switro'),
                'type'        => 'text',
                'description' => __('Title shown to customers during checkout.', 'wc-solana-pay-by-switro'),
                'default'     => __('Pay using Solana', 'wc-solana-pay-by-switro'),
                'desc_tip'    => true,
            ],
            'description' => [
                'title'       => __('Description', 'wc-solana-pay-by-switro'),
                'type'        => 'textarea',
                'description' => __('Displayed under the payment title on checkout.', 'wc-solana-pay-by-switro'),
                'default'     => __('Pay instantly with Switro Solana Wallet.', 'wc-solana-pay-by-switro'),
                'desc_tip'    => true,
            ],
            'api_key' => [
                'title'       => __('API Key', 'wc-solana-pay-by-switro'),
                'type'        => 'password',
                'description' => __('To obtain your API Key, go to: ', 'wc-solana-pay-by-switro') . '<a href="https://switro.com/" target="_blank">Switro.com</a>.',
                'default'     => '',
                'desc_tip'    => false,
            ],
            'network' => [
                'title'       => __('Network', 'wc-solana-pay-by-switro'),
                'type'        => 'select',
                'description' => __('Choose the Solana network environment.', 'wc-solana-pay-by-switro'),
                'default'     => 'mainnet',
                'desc_tip'    => true,
                'options'     => [
                    'mainnet' => __('Mainnet', 'wc-solana-pay-by-switro'),
                    'devnet'  => __('Devnet', 'wc-solana-pay-by-switro'),
                ],
            ],
            'webhook_info' => [
                'title' => __('Webhook URL', 'wc-solana-pay-by-switro'),
                'type'  => 'text',
                'custom_attributes' => [
                    'readonly' => 'readonly',
                ],
                'default' => esc_url(site_url('/wp-json/switro/v1/webhook')),
                'class' => 'switro-webhook-url',
                'description' => '<button type="button" class="button" id="copy-webhook-btn" style="margin-top: 8px;">Copy to Clipboard</button> <br>
                    Please copy the Webhook URL and add it to your Switro API dashboard.',
            ],
        ];
    }

    public function is_available()
    {
        return 'yes' === $this->enabled;
    }

    public function process_payment($order_id)
    {
        $order = wc_get_order($order_id);

        $redirect_url = $this->get_switro_payment_redirect_url($order);

        if ($redirect_url) {
            return [
                'result' => 'success',
                'redirect' => $redirect_url,
            ];
        } else {
            wc_add_notice(__('Switro payment failed. Please try again.', 'wc-solana-pay-by-switro'), 'error');
            return [
                'result' => 'failure',
            ];
        }
    }

    private function get_switro_payment_redirect_url($order)
    {
        if (!$order) {
            wc_add_notice(__('Payment error: Invalid order.', 'wc-solana-pay-by-switro'), 'error');
            return false;
        }

        // Ensure API key is set
        $api_key = trim($this->api_key);
        if (empty($api_key)) {
            wc_add_notice(__('Payment error: API key is not configured.', 'wc-solana-pay-by-switro'), 'error');
            return false;
        }

        $items = [];

        foreach ($order->get_items() as $item) {
            $product = $item->get_product();

            $items[] = [
                'item_title'       => $item->get_name(),
                'item_quantity'    => $item->get_quantity(),
                'item_amount'      => (float) $item->get_total(),
                'item_description' => $product ? $product->get_short_description() : '',
                'item_image_url'   => $product && $product->get_image_id() ? wp_get_attachment_url($product->get_image_id()) : '',
            ];
        }


        $order_id = $order->get_id();
        $success_url = add_query_arg(['switro_status' => 'success', 'order_id' => $order_id], $this->get_return_url($order));
        $cancel_url = add_query_arg(['switro_status' => 'cancelled', 'order_id' => $order_id], $order->get_cancel_order_url());
        $amount_shipping = (float) $order->get_shipping_total();
        $amount_tax = (float) $order->get_total_tax();

        $payload = [
            'customer_name'    => $order->get_billing_first_name() . ' ' . $order->get_billing_last_name(),
            'customer_email'   => $order->get_billing_email(),
            'customer_phone'   => $order->get_billing_phone(),
            'customer_address' => $order->get_billing_address_1(),
            'amount_total'     => (float) $order->get_total(),
            'amount_currency'  => $order->get_currency(),
            'cancel_url'       => $cancel_url,
            'success_url'      => $success_url,
            'items'            => $items,
        ];

        if ($amount_shipping >= 0.01) {
            $payload['amount_shipping'] = $amount_shipping;
        }

        if ($amount_tax >= 0.01) {
            $payload['amount_tax'] = $amount_tax;
        }

        $response = wp_remote_post('https://switro.com/api/v1/checkout', [
            'method'  => 'POST',
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . $api_key,
                'x-network'     => trim($this->network ?? 'mainnet'),
            ],
            'body'    => json_encode($payload),
            'timeout' => 30,
        ]);

        // error_log('Response from Switro: ' . print_r($response, true));
        if (is_wp_error($response)) {
            wc_add_notice(__('Payment error: Could not connect to Switro API.', 'wc-solana-pay-by-switro'), 'error');
            error_log('[Switro] Error: ' . $response->get_error_message());
            return false;
        }

        $body = json_decode(wp_remote_retrieve_body($response), true);

        if (!empty($body['url']) && !empty($body['id'])) {
            $order->update_meta_data('_switro_checkout_id', sanitize_text_field($body['id']));
            $order->save();

            return esc_url($body['url']);
        } else {
            $errorMsg = __('Invalid response from Switro.', 'wc-solana-pay-by-switro');
            if (!empty($body['message'])) {
                $errorMsg = is_array($body['message']) ? implode(', ', $body['message']) : $body['message'];
            }
            // wc_add_notice(__('Payment error: ', 'wc-solana-pay-by-switro') . esc_html($errorMsg), 'error');
            error_log('[Switro] Error: ' . esc_html($errorMsg));
            return false;
        }
    }
}
