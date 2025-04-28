# DatabunkerPro PHP Client Library

[![Latest Version](https://img.shields.io/packagist/v/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Total Downloads](https://img.shields.io/packagist/dt/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)
[![Build Status](https://github.com/securitybunker/databunkerpro-php/actions/workflows/ci.yml/badge.svg)](https://github.com/securitybunker/databunkerpro-php/actions)
[![License](https://img.shields.io/packagist/l/securitybunker/databunkerpro-php.svg?style=flat-square)](https://packagist.org/packages/securitybunker/databunkerpro-php)

Official PHP client library for the DatabunkerPro API.

## Requirements

- PHP 5.6 or higher
- JSON extension

## Installation

Install the package using Composer:

```bash
composer require databunkerpro/databunkerpro-php
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

## Available Methods

The library provides methods for all DatabunkerPro API endpoints:

- User Management
- App Data Management
- Legal Basis Management
- Agreement Management
- Processing Activity Management
- Connector Management
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
