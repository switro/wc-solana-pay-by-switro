=== WC Solana Pay By Switro ===
Contributors: switro
Tags: solana, crypto payments, woocommerce, wallet, usdc
Requires at least: 6.0
Tested up to: 6.8
Requires PHP: 7.4
Requires Plugins: woocommerce
Stable tag: 1.0.0
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

Accept Solana payments instantly on your WooCommerce store with Switro – a modern, non-custodial crypto payment gateway.

== Description ==

**WC Solana Pay By Switro** enables WooCommerce merchants to accept **Solana wallet-to-wallet payments** directly, without intermediaries or withdrawal delays. Funds go straight to your own wallet, giving you full control over your crypto revenue.

With seamless integration, low fees, and built-in security, Switro makes it simple to start accepting crypto payments today.

**Key Features:**
- ⚡ **Instant Wallet-to-Wallet Transfers** – Payments are settled immediately on the Solana blockchain.  
- 🔒 **Non-Custodial & Secure** – Funds are never held by third parties.  
- 💱 **Optional USDC Conversion** – Automatically convert SOL payments to USDC to minimize volatility risk.  
- 🛡 **Slippage Control** – Protects against market volatility during conversions.  
- 🔗 **On-Chain Settlement** – Every transaction is transparent and verifiable on Solana.  
- 🌐 **Webhook Integration** – Real-time payment notifications to automate your order processing.  
- 🎯 **Fully Compatible with WooCommerce** – Simple setup, no coding required.  

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/wc-solana-pay-by-switro` directory or install via WordPress plugin admin.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Ensure WooCommerce is installed and activated.
4. Navigate to **WooCommerce > Settings > Payments** and enable **Switro Solana Wallet**.
5. Configure the plugin with your Switro API key.

== Configuration Steps ==
1. **Get your API Key**  
   - Login to your Switro account → **API Settings** → **Copy API Key**.  
2. **Configure in WordPress**  
   - Go to **WooCommerce → Settings → Payments → Switro Solana Wallet**.  
   - Paste the copied API Key into the API Key field and save changes.  
3. **Set up Webhooks**  
   - From the WordPress admin → **Payments → Switro Solana Wallet**, copy the **Webhook URL** provided.  
   - Go back to **Switro Dashboard → API Settings** → paste the Webhook URL → **Save/Update Webhook**.  
4. Your store is now ready to accept Solana payments!

== Frequently Asked Questions ==

= Do I need a Switro account? =
Yes, you’ll need to create a free account on [Switro](https://www.switro.com/auth/register) to obtain your API keys and manage payment settings.

= Is this plugin non-custodial? =
Yes! Payments go directly to your Solana wallet. Switro does not hold or control your funds.

= Does it support USDC? =
Yes, you can enable optional automatic conversion to USDC to reduce volatility risk.


== Screenshots ==

1. **Checkout page with Solana payment option**  
2. **Payment settings in WooCommerce admin**

== References ==

- **Get Started with Switro:** [https://www.switro.com/auth/register](https://www.switro.com/auth/register)  
- **Switro API & Developer Docs:** [https://switro.com/docs](https://switro.com/docs)

== Changelog ==

= 1.0.0 =
* Initial release with support for Solana wallet payments, USDC conversion, and webhook notifications.

== Upgrade Notice ==

= 1.0.0 =
First stable version – start accepting Solana payments on WooCommerce today!

== License ==

This plugin is licensed under the GPLv2 or later. See [License URI](https://www.gnu.org/licenses/gpl-2.0.html) for details.
