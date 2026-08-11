# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

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

### Removed
- `preloginUser`, `loginUser`, `createCaptcha`, `getUIConf` and `getTenantConf`. These wrapped
  internal portal endpoints that are not part of the public API. `getTenantConf` additionally
  called `TenantGetConf`, which does not exist on the server.

### Changed
- Development dependency `squizlabs/php_codesniffer` raised to `^4.0`

## [1.0.0] - 2024-03-21

### Added
- Initial release of DatabunkerPro PHP Client
- Support for PHP 5.6 and higher
- Basic API client functionality
- Comprehensive test suite
- GitHub Actions CI workflow
- Code quality tools (PHPUnit, PHP_CodeSniffer) 