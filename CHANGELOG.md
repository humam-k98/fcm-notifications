# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [2.0.0] - 2025-05-25

### Added
- **FCM HTTP v1 API Support** - OAuth 2.0 authentication with service account keys
- New `FcmOAuthProvider` class for handling OAuth authentication
- Support for Google Auth library integration
- Enhanced error handling with detailed response information
- Batch processing for multiple device tokens with concurrent requests
- New environment variables for v1 API configuration:
  - `FCM_API_VERSION` - Choose between 'v1' and 'legacy'
  - `FCM_PROJECT_ID` - Firebase project ID (required for v1)
  - `FCM_SERVICE_ACCOUNT_KEY_PATH` - Path to service account key file
- Enhanced message format methods:
  - `toV1Array()` - Convert message to v1 API format
  - `toV1BatchArray()` - Convert message for batch processing
- Comprehensive migration guide
- Example files demonstrating usage patterns
- Laravel 11 compatibility

### Changed
- **BREAKING**: Updated `FcmNotificationSender` to support both API versions
- Enhanced `FcmMessage` class with v1 API support
- Updated configuration file structure to support both API versions
- Improved error responses with detailed failure information
- Updated composer dependencies to include Google Auth library

### Improved
- Better error handling and debugging information
- Enhanced security with OAuth 2.0 authentication
- More efficient batch processing for multiple tokens
- Comprehensive documentation and examples
- Better type safety with improved method signatures

### Backwards Compatibility
- Full backwards compatibility with legacy server key authentication
- Existing applications continue to work without changes
- Legacy API remains supported for gradual migration

### Security
- OAuth 2.0 authentication for enhanced security
- Service account key-based authentication
- Removal of dependency on deprecated server keys (when using v1 API)

### Migration
- Added detailed migration guide for upgrading to v1 API
- Step-by-step instructions for Firebase console setup
- Environment variable configuration examples

## [1.x.x] - Previous Versions

### Legacy Features
- Basic FCM HTTP API support with server key authentication
- Laravel notification channel integration
- Topic-based messaging
- Device token management
- Basic error handling
