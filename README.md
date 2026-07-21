# DatabunkerPro PHP Client Library

[![Latest Version](https://img.shields.io/packagist/v/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Total Downloads](https://img.shields.io/packagist/dt/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Build Status](https://github.com/securitybunker/databunkerpro-php/actions/workflows/ci.yml/badge.svg)](https://github.com/securitybunker/databunkerpro-php/actions)
[![License](https://img.shields.io/packagist/l/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)

Official PHP client library for the DatabunkerPro API.

## Requirements

- PHP 5.6 or higher
- JSON extension
- cURL extension

## Installation

Install the package using Composer:

```bash
composer require securitybunker/databunkerpro-php
```

## Usage

```php
<?php

require 'vendor/autoload.php';

use DatabunkerPro\DatabunkerproAPI;

// Initialize the client
$api = new DatabunkerproAPI(
    'https://your-databunker-instance.com',
    'your-x-bunker-token',
    'your-tenant-id'
);

// Create a user
$result = $api->createUser([
    'email' => 'user@example.com',
    'name' => 'John Doe'
]);

// Get user information
$user = $api->getUser('email', 'user@example.com');

// Update user
$api->updateUser('email', 'user@example.com', [
    'name' => 'John Smith'
]);
```

### TLS certificate verification

By default the client **verifies the server's TLS certificate** on every request. This is the
secure default and should be left on in production. If you run a self-hosted Databunker instance
with a self-signed certificate in a trusted/development network, you can opt out by passing
`false` as the fourth constructor argument:

```php
// Verify TLS certificates (default, recommended)
$api = new DatabunkerproAPI($baseURL, $token, $tenant);

// Disable verification — only for self-signed certs on a trusted network
$api = new DatabunkerproAPI($baseURL, $token, $tenant, false);
```

Disabling verification exposes your traffic (access token and personal data) to
man-in-the-middle interception, so avoid it against any public endpoint.

## Available Methods

The library provides methods for all DatabunkerPro API endpoints:

- User Management
- App Data Management
- Legal Basis Management
- Agreement Management
- Processing Activity Management
- Group Management
- Token Management
- Audit Management
- Tenant Management
- Role Management
- Policy Management
- Session Management

For detailed API documentation, please refer to the [DatabunkerPro API Documentation](https://databunker.org/databunker-pro-docs/introduction/).

## Testing

```bash
composer test
```

## Code Quality

Run static analysis:

```bash
composer phpstan
```

Check code style:

```bash
composer cs-check
```

Fix code style issues:

```bash
composer cs-fix
```

## Security

This library is scanned on every push and pull request, with a weekly scheduled sweep to catch drift:

- **SAST (Semgrep):** static analysis using the `p/php`, `p/secrets`, `p/security-audit`, and `p/owasp-top-ten` rulesets. A finding fails the check, and results are published to the repository's **Code Scanning** tab. See [`.github/workflows/semgrep.yml`](.github/workflows/semgrep.yml).
- **Supply-chain hardening:** every GitHub Action is pinned to a full commit SHA, so a mutable tag (`@v4`) cannot be silently repointed to malicious code.
- **TLS on by default:** the client verifies server certificates unless you explicitly opt out (see [TLS certificate verification](#tls-certificate-verification)).

Reproduce the SAST scan locally:

```bash
pip install semgrep
semgrep scan \
    --config p/php \
    --config p/secrets \
    --config p/security-audit \
    --config p/owasp-top-ten
```

To report a security vulnerability, please email hello@databunker.org rather than opening a public issue.

## Contributing

1. Fork the repository
2. Create your feature branch (`git checkout -b feature/amazing-feature`)
3. Commit your changes (`git commit -m 'Add some amazing feature'`)
4. Push to the branch (`git push origin feature/amazing-feature`)
5. Open a Pull Request

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

For support, please contact hello@databunker.org or open an issue in the GitHub repository.
