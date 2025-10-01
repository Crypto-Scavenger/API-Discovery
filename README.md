# API & Discovery

Control WordPress APIs and discovery features for enhanced security and privacy.

## Description

API & Discovery is a lightweight WordPress plugin that allows you to selectively disable various WordPress APIs and discovery features. This plugin helps improve security by reducing attack vectors and removing unnecessary metadata from your site.

## Features

- **Disable REST API (Frontend)** - Removes REST API links from frontend while keeping API functional
- **Disable XML-RPC** - Disables the legacy XML-RPC protocol to reduce security risks
- **Disable Really Simple Discovery (RSD)** - Removes RSD links for deprecated blog clients
- **Disable Windows Live Writer Manifest** - Removes support for obsolete Microsoft software
- **Disable RSS Feed Links** - Removes feed links from site header (feeds still work if accessed directly)
- **Disable RSS Feeds Completely** - Completely disables all RSS feeds
- **Disable Feed Generator Tags** - Removes WordPress version information from RSS feeds
- **Data Cleanup Option** - Choose whether to remove plugin data on uninstall

## Installation

1. Upload the `api-discovery` folder to the `/wp-content/plugins/` directory
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Go to Tools > API & Discovery to configure settings

## Usage

After activation, navigate to **Tools > API & Discovery** in your WordPress admin panel. Check the boxes for the features you want to disable and click "Save Changes".

### Settings Overview

**REST API Frontend**
- When enabled: Removes REST API discovery links from your site's header
- API remains functional for admin and authenticated requests
- Recommended for sites not using headless/decoupled architecture

**XML-RPC**
- When enabled: Completely disables the XML-RPC interface
- Improves security by removing a common attack vector
- Only disable if you don't use Jetpack, mobile apps, or pingbacks

**RSD & Windows Live Writer**
- When enabled: Removes legacy blog client discovery mechanisms
- Safe to disable on modern WordPress installations
- No impact on current functionality

**Feed Settings**
- **Feed Links**: Removes auto-discovery but feeds remain accessible
- **Complete Disable**: Returns 410 Gone status for all feed requests
- **Generator Tags**: Removes WordPress version from feeds for security

**Data Cleanup**
- When enabled: Plugin removes all settings and database tables on uninstall
- When disabled: Settings are preserved if you reinstall the plugin

## Technical Details

### Database

The plugin creates a custom database table `wp_api_discovery_settings` to store configuration without bloating the wp_options table.

### Performance

- Settings are cached using WordPress transients for optimal performance
- Lazy loading prevents unnecessary database queries
- Minimal overhead with conditional hook registration

### Security

- All settings are protected by WordPress nonces
- Capability checks on all admin operations
- Prepared statements for all database queries
- Input sanitization and output escaping

## File Structure

```
api-discovery/
├── api-discovery.php          # Main plugin file
├── README.md                  # This file
├── uninstall.php             # Cleanup handler
├── index.php                 # Security stub
├── assets/
│   ├── admin.css             # Admin styles
│   └── index.php             # Security stub
└── includes/
    ├── class-database.php    # Database operations
    ├── class-core.php        # Core functionality
    ├── class-admin.php       # Admin interface
    └── index.php             # Security stub
```

## Requirements

- WordPress 5.0 or higher
- PHP 7.4 or higher
- MySQL 5.0 or higher (MySQL 8.0+ recommended)

## Compatibility

- Works with all modern WordPress themes
- Compatible with major caching plugins
- No conflicts with Gutenberg block editor
- Multisite compatible

## Changelog

### 1.0.0
- Initial release
- 7 API/discovery disable options
- Custom database table for settings
- Clean admin interface
- Conditional data cleanup on uninstall

## License

This plugin is licensed under GPL v2 or later.
