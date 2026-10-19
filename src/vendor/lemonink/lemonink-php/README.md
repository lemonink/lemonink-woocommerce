# LemonInk API Client for PHP

## Getting started

1. **Sign up for LemonInk** — Before you begin, you need to sign up for a [LemonInk](https://lemonink.co/register) account and obtain an API key.
2. **Minimum requirements** — To run the SDK, your system will need to meet the minimum requirements, including having PHP >= 5.5.

## Example

````PHP
<?php

require "vendor/autoload.php";

$client = new LemonInk\Client("your-api-key");

$transaction = new LemonInk\Models\Transaction();
$transaction->setMasterId("id-of-a-master-file");
$transaction->setWatermarkValue("Text you want to have embedded in your file");

$client->save($transaction);

echo "Download your file from {$transaction->getUrl()}\n";

````

### Readium LCP

On an account (or master) that protects files with Readium LCP, pass a stable
`customer_ref` so every book one buyer purchases opens with the same passphrase.
Without it each transaction gets its own passphrase. Never derive the ref from an
email address the buyer typed: anyone could type someone else's and read their
passphrase.

````PHP
$transaction->setLcpParams(["customer_ref" => "my-store/customer:42"]);
````
