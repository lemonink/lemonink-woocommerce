=== LemonInk Ebook Watermarking for WooCommerce ===
Contributors: lemonink
Tags: lemonink, ecommerce, e-commerce, downloadable, downloads, ebooks, drm, watermark, watermarking, epub, mobi, pdf, Kindle, woocommerce
Requires at least: 4.4
Tested up to: 5.3.2
Stable tag: 0.3.1
License: MIT

Watermark EPUB, MOBI and PDF files in your WooCommerce store using the LemonInk service.

== Description ==

Watermark EPUB, MOBI and PDF files in your WooCommerce store using the LemonInk service.

[LemonInk](https://www.lemonink.co/how-to-use/woocommerce) is a cloud service used to secure digital books from piracy. It applies a digital watermark to each purchased ebook making it unique and traceable.

Using this plugin, you can easily integrate LemonInk into your WooCommerce store just by marking products as downloadable and assigning a master file (the original ebook) to them.

After each purchase LemonInk will create watermarked versions of your ebooks and attach them to user's order allowing them to easily download their individual copies.

Note that you need to have an account at [LemonInk](https://www.lemonink.co), but you can easily set it up just by [registering](https://www.lemonink.co/register). In order to watermark files you'll also need to purchase some credits, but if you just wish to give it a try, there's a test mode available.

For more information go to [LemonInk](https://www.lemonink.co) or drop us a line at <hello@lemonink.co>.

== Installation ==

1. Upload the plugin files to the `/wp-content/plugins/plugin-name` directory, or install the plugin through the WordPress plugins screen directly.
1. Activate the plugin through the 'Plugins' screen in WordPress
1. Go to [API keys section at lemonink.co](https://www.lemonink.co/account/api-keys) and generate a new API key. Copy the token.
1. Go to WooCommerce->Settings->Integration->LemonInk screen, paste the copied token and save.
1. Add a master file at [lemonink.co](https://www.lemonink.co/masters) and copy it's ID.
1. Edit the product you wish to watermark using LemonInk, mark it as Downloadable, make sure that "Watermark downloads using LemonInk" checkbox is checked and paste the ID into the "Master file ID field". Save.

Any subsequent purchases of this product will be served watermarked versions of your ebook.

Enjoy!

For more information go to [LemonInk](https://www.lemonink.co) or drop us a line at <hello@lemonink.co>.