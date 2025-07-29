<?php

if (!defined('ABSPATH')) {
    exit;
}

class WC_Gateway_Switro_Handler
{

    public function __construct()
    {
        add_action('rest_api_init', [$this, 'register_webhook']);
        add_action('add_meta_boxes', [$this, 'add_meta_box']);
    }

    public function register_webhook()
    {
        register_rest_route('switro/v1', '/webhook', [
            'methods'  => 'POST',
            'callback' => [$this, 'handle_webhook'],
            'permission_callback' => '__return_true',
        ]);
    }

    public function handle_webhook(WP_REST_Request $request)
    {

        $body = $request->get_json_params();

        // Log the raw JSON payload for debugging
        // error_log('[Switro Webhook] Payload: ' . print_r($body, true));

        if (empty($body['checkout_id']) || empty($body['payment_id'])) {
            return new WP_REST_Response(['error' => 'Invalid payload'], 400);
        }

        $checkout_id = sanitize_text_field($body['checkout_id']);
        $payment_id  = sanitize_text_field($body['payment_id']);

        // Find the order by checkout_id
        $orders = wc_get_orders([
            'limit'        => 1,
            'meta_key'     => '_switro_checkout_id',
            'meta_value'   => $checkout_id,
            'meta_compare' => '=',
        ]);

        if (empty($orders)) {
            return new WP_REST_Response(['error' => 'Order not found'], 404);
        }

        $order = $orders[0];

        $available_gateways = WC()->payment_gateways->get_available_payment_gateways();
        $gateway = $available_gateways['switro_gateway'] ?? null;

        if (!$gateway) {
            return new WP_REST_Response(['error' => 'Gateway not available'], 403);
        }
        $api_key = trim($gateway->get_option('api_key'));

        if (empty($api_key)) {
            return new WP_REST_Response(['error' => 'API key not configured'], 403);
        }

        // Call Switro API to verify payment
        $response = wp_remote_get("https://www.switro.com/api/v1/payment/{$payment_id}", [
            'headers' => [
                'Authorization' => 'Bearer ' . $api_key,
            ],
            'timeout' => 20,
        ]);

        if (is_wp_error($response)) {
            error_log('[Switro Webhook] WP_Error: ' . $response->get_error_message());
            return new WP_REST_Response(['error' => 'Failed to contact Switro'], 500);
        }

        $payment = json_decode(wp_remote_retrieve_body($response), true);

        if (empty($payment) || empty($payment['status'])) {
            return new WP_REST_Response(['error' => 'Invalid payment response'], 422);
        }

        // Update order based on status
        if ($payment['status'] === 'confirmed') {
            $order->payment_complete($payment_id);
            $order->add_order_note("Switro Payment Confirmed.\nTransaction: " . esc_url($payment['transaction_url']));
            $order->update_meta_data('_switro_payment_id', $payment_id);
            $order->update_meta_data('_switro_network', sanitize_text_field($payment['network']));
            $order->update_meta_data('_switro_amount_original', sanitize_text_field($payment['amount_original']));
            $order->save();
        } else {
            $order->update_status('failed', __('Switro payment failed.', 'wc-solana-pay-by-switro'));
            if (!empty($payment['transaction_error'])) {
                $order->add_order_note('Switro Error: ' . sanitize_text_field($payment['transaction_error']));
            }
        }

        return new WP_REST_Response(['success' => true], 200);
    }

    public function add_meta_box()
    {
        add_meta_box('switro_meta', 'Switro Payment Info', [$this, 'render_meta_box'], 'shop_order', 'side');
    }

    public function render_meta_box($post)
    {
        $checkout_id = get_post_meta($post->ID, '_switro_checkout_id', true);
        $payment_id  = get_post_meta($post->ID, '_switro_payment_id', true);
        $network     = get_post_meta($post->ID, '_switro_network', true);
        $amount      = get_post_meta($post->ID, '_switro_amount_original', true);

        if (empty($checkout_id)) {
            echo '<p>No Switro payment data available for this order.</p>';
            return;
        }

        echo "<p><strong>Checkout ID:</strong> " . esc_html($checkout_id) . "</p>";
        echo "<p><strong>Payment ID:</strong> " . esc_html($payment_id) . "</p>";
        echo "<p><strong>Network:</strong> " . esc_html($network) . "</p>";
        echo "<p><strong>Original Amount:</strong> " . esc_html($amount) . "</p>";
    }
}
