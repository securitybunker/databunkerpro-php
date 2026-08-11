# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.1.0] - 2026-08-11

Note: this release removes five public methods and renames two. They wrapped internal
portal endpoints that were not part of the public API. See **Removed** and **Fixed**
below before upgrading from 1.0.0.

### Added
- File Storage API: `createFile`, `getFile`, `listUserFiles`, `replaceFileTags`, `deleteFile`
- `bulkListFilesByTag` to list files carrying a tag across all users in a tenant
- Optional `tag` filter on `listUserFiles`
- `bulkListAllUsers`, `deleteUsersBulk`, `listUserVersions`, `deleteAppData`, `listAppDataVersions`

### Fixed
- `bulkListUserRequests` and `bulkListAuditEvents` called `BulkListUserRequests` and
  `BulkListAuditEvents`, which do not exist on the server. They now call
  `BulkListAllUserRequests` and `BulkListAllAuditEvents`, and were renamed to
  `bulkListAllUserRequests` and `bulkListAllAuditEvents` to match.
- Removed `curl_close()` calls, which PHP 8.5 deprecates and which have been a no-op
  since PHP 8.0. They emitted a deprecation notice on every request under PHP 8.5.

### Removed
- `preloginUser`, `loginUser`, `createCaptcha`, `getUIConf` and `getTenantConf`. These wrapped
  internal portal endpoints that are not part of the public API. `getTenantConf` additionally
  called `TenantGetConf`, which does not exist on the server.

### Changed
- Development dependencies raised: `phpunit/phpunit` to `^9.6`, `squizlabs/php_codesniffer` to `^4.0`
- CI now tests against PHP 7.3, 7.4, 8.0, 8.1, 8.2 and 8.3. PHP 7.3 remains the minimum
  supported version — the client uses no syntax above it.
- README corrected: it claimed PHP 5.6 while `composer.json` has required 7.3 since 1.0.0

## [1.0.0] - 2024-03-21

### Added
- Initial release of DatabunkerPro PHP Client
- Support for PHP 5.6 and higher
- Basic API client functionality
- Comprehensive test suite
- GitHub Actions CI workflow
- Code quality tools (PHPUnit, PHP_CodeSniffer) 