# DatabunkerPro PHP Client Library

[![Latest Version](https://img.shields.io/packagist/v/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Total Downloads](https://img.shields.io/packagist/dt/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Build Status](https://github.com/securitybunker/databunkerpro-php/actions/workflows/ci.yml/badge.svg)](https://github.com/securitybunker/databunkerpro-php/actions)
[![License](https://img.shields.io/packagist/l/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)

Official PHP client library for the DatabunkerPro API.

## Requirements

- PHP 7.3 or higher
- JSON extension
- cURL extension

## Installation

Install the package using Composer:

```bash
composer require securitybunker/databunkerpro-php
```

## Quickstart

You need a Databunker Pro instance to talk to. Demo mode gives you one in a single command — no database, no configuration, everything held in memory:

```bash
docker run -p 3000:3000 -d --rm --name databunkerpro securitybunker/databunkerpro demo
```

Check that it came up:

```bash
docker logs databunkerpro
```

```
 Databunker Pro demo is ready
  Web UI:            http://localhost:3000/
  Root access token: DEMO
  Database:          in-memory, erased on restart
```

The root access token in demo mode is the fixed string `DEMO`. Save this as `quickstart.php`:

```php
<?php
require __DIR__ . '/vendor/autoload.php';

use DatabunkerPro\DatabunkerproApi;

$api = new DatabunkerproApi('http://localhost:3000', 'DEMO');

// Create a user record. The vault encrypts the profile and returns a user token.
$created = $api->createUser([
    'email' => 'john@phptest.com',
    'name'  => 'John Doe',
    'phone' => '+15551234567',
]);
echo "User token: " . $created['token'] . "\n";

// Read the record back by any indexed field: token, login, email, phone, custom.
$user = $api->getUser('email', 'john@phptest.com');
echo "Profile: " . json_encode($user['profile']) . "\n";

// Store an encrypted file against that user, tagged by document type.
$file = $api->createFile(
    'email',
    'john@phptest.com',
    'passport.jpg',
    base64_encode('fake passport scan bytes'),
    ['tags' => ['passport', 'kyc']]
);
echo "File uuid: " . $file['fileuuid'] . " | tags: " . implode(',', $file['tags']) . "\n";

// List the user's files, filtered by tag.
$listing = $api->listUserFiles('email', 'john@phptest.com', 'kyc');
echo "Files tagged kyc: " . implode(',', array_column($listing['files'], 'filename')) . "\n";

// Fetch the file back. Content returns base64-encoded in filedata.
$fetched = $api->getFile('email', 'john@phptest.com', ['fileuuid' => $file['fileuuid']]);
echo "Decrypted: " . base64_decode($fetched['filedata']) . "\n";

// Delete user record.
$api->deleteUser('email', 'john@phptest.com');
echo "User deleted\n";
```

```bash
php quickstart.php
```

```
User token: a58765a6-22bc-a806-a1b8-b71205f235fd
Profile: {"email":"john@phptest.com","name":"John Doe","phone":"+15551234567"}
File uuid: 1ca799e7-1554-e559-96d9-4f8a64be9624 | tags: kyc,passport
Files tagged kyc: passport.jpg
Decrypted: fake passport scan bytes
User deleted
```

Tags are lowercased, de-duplicated and sorted on write, which is why they come back in a different order than they were sent.

When you are done, stop the instance. It was started with `--rm`, so the container and its in-memory database are discarded:

```bash
docker stop databunkerpro
```

> **Demo mode is for evaluation only.** The database is in memory, the wrapping key is a fixed public value, and the root token is the well-known string `DEMO`. Never point it at real personal data. For a real deployment see the [installation guide](https://docs.databunker.org/pro/installation/docker-compose).

## Usage

```php
<?php

require 'vendor/autoload.php';

use DatabunkerPro\DatabunkerproApi;

// Initialize the client
$api = new DatabunkerproApi(
    'https://your-databunker-instance.com',
    'your-x-bunker-token',
    'your-tenant-id'
);

// Create a user
$result = $api->createUser([
    'email' => 'john@phptest.com',
    'name' => 'John Doe'
]);

// Get user information
$user = $api->getUser('email', 'john@phptest.com');

// Update user
$api->updateUser('email', 'john@phptest.com', [
    'name' => 'John Smith'
]);
```

### File storage

Files are attached to a user and can carry tags for later lookup:

```php
// Store a file against a user, tagged for later retrieval
$api->createFile('email', 'user@example.com', 'passport.pdf', base64_encode($bytes), [
    'mimetype' => 'application/pdf',
    'tags' => ['identity-docs']
]);

// List that user's files, optionally filtered by tag
$files = $api->listUserFiles('email', 'user@example.com', 'identity-docs');

// Fetch one back by name or uuid
$file = $api->getFile('email', 'user@example.com', ['filename' => 'passport.pdf']);

// Replace the complete tag set on a file
$api->replaceFileTags('email', 'user@example.com', $file['fileuuid'], ['archived']);

$api->deleteFile('email', 'user@example.com', $file['fileuuid']);
```

Files can also be listed by tag across every user in the tenant, which needs a bulk-unlock uuid:

```php
$unlock = $api->bulkListUnlock();
$tagged = $api->bulkListFilesByTag($unlock['unlockuuid'], 'identity-docs', 0, 100);
```

### TLS certificate verification

By default the client **verifies the server's TLS certificate** on every request. This is the
secure default and should be left on in production. If you run a self-hosted Databunker instance
with a self-signed certificate in a trusted/development network, you can opt out by passing
`false` as the fourth constructor argument:

```php
// Verify TLS certificates (default, recommended)
$api = new DatabunkerproApi($baseURL, $token, $tenant);

// Disable verification — only for self-signed certs on a trusted network
$api = new DatabunkerproApi($baseURL, $token, $tenant, false);
```

Disabling verification exposes your traffic (access token and personal data) to
man-in-the-middle interception, so avoid it against any public endpoint.

## Available Methods

The library provides methods for all DatabunkerPro API endpoints:

- User Management
- App Data Management
- File Storage
- Tokenization
- Legal Basis & Agreement Management
- Processing Activity Management
- Group Management
- Role & Policy Management
- Session Management
- Shared Records
- Bulk Operations
- Audit Management
- Tenant Management
- Authentication & Access Tokens
- System Operations

For detailed API documentation, please refer to the [DatabunkerPro API Documentation](https://docs.databunker.org/pro/get-started/overview).

## Testing

```bash
composer test
```

## Code Quality

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
