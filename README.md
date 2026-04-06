# Catch-All Email Verifier - PHP Library

[![Packagist Version](https://img.shields.io/packagist/v/enrow/catch-all-email-verifier)](https://packagist.org/packages/enrow/catch-all-email-verifier)
[![License: MIT](https://img.shields.io/badge/License-MIT-green.svg)](https://opensource.org/licenses/MIT)
[![GitHub stars](https://img.shields.io/github/stars/EnrowAPI/catch-all-email-verifier-php)](https://github.com/EnrowAPI/catch-all-email-verifier-php)
[![Last commit](https://img.shields.io/github/last-commit/EnrowAPI/catch-all-email-verifier-php)](https://github.com/EnrowAPI/catch-all-email-verifier-php/commits)

Verify emails on catch-all domains with deterministic verification. Most verifiers mark catch-all emails as "risky" or "unknown" -- this one tells you if the specific mailbox actually exists.

Powered by [Enrow](https://enrow.io) -- deterministic email verification, not probabilistic.

## The catch-all problem

A catch-all (or accept-all) domain is configured to accept mail sent to any address at that domain, whether or not the specific mailbox exists. This means `anything@company.com` will not bounce at the SMTP level, so traditional email verifiers cannot distinguish real inboxes from non-existent ones. They return "accept-all", "risky", or "unknown" and leave you guessing.

Enrow uses deterministic verification techniques that go beyond SMTP handshake checks, resolving the actual mailbox existence on catch-all domains. The result is a clear valid/invalid verdict instead of an inconclusive shrug.

## Installation

```bash
composer require enrow/catch-all-email-verifier
```

Requires PHP 8.1+ and Guzzle 7.

## Simple Usage

```php
use CatchAllVerifier\CatchAllVerifier;

$verification = CatchAllVerifier::verify('your_api_key', [
    'email' => 'tcook@apple.com',
]);

$result = CatchAllVerifier::get('your_api_key', $verification['id']);

echo $result['email'];         // tcook@apple.com
echo $result['qualification']; // valid
```

`verify()` returns a verification ID. The verification runs asynchronously -- call `get()` to retrieve the result once it's ready. You can also pass a `webhook` URL to get notified automatically.

## Bulk verification

```php
use CatchAllVerifier\CatchAllVerifier;

$batch = CatchAllVerifier::verifyBulk('your_api_key', [
    'verifications' => [
        ['email' => 'tcook@apple.com'],
        ['email' => 'satya@microsoft.com'],
        ['email' => 'jensen@nvidia.com'],
    ],
]);

// $batch['batchId'], $batch['total'], $batch['status']

$results = CatchAllVerifier::getBulk('your_api_key', $batch['batchId']);
// $results['results'] -- array of verification results
```

Up to 5,000 verifications per batch. Pass a `webhook` URL to get notified when the batch completes.

## Error handling

```php
try {
    CatchAllVerifier::verify('bad_key', [
        'email' => 'test@test.com',
    ]);
} catch (\RuntimeException $e) {
    // $e->getMessage() contains the API error description
    // Common errors:
    // - "Invalid or missing API key" (401)
    // - "Your credit balance is insufficient." (402)
    // - "Rate limit exceeded" (429)
}
```

## Getting an API key

Register at [app.enrow.io](https://app.enrow.io) to get your API key. You get **50 free credits** (= 200 verifications) with no credit card required. Each verification costs **0.25 credits**.

Paid plans start at **$17/mo** for 1,000 credits up to **$497/mo** for 100,000 credits. See [pricing](https://enrow.io/pricing).

## Documentation

- [Enrow API documentation](https://docs.enrow.io)
- [Full Enrow SDK](https://github.com/EnrowAPI/enrow-php) -- includes email finder, phone finderand more

## License

MIT -- see [LICENSE](LICENSE) for details.
