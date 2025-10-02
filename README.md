# Network & Performance Tweaks

**Version:** 1.0.1  
**License:** GPL v2 or later

## Description

Network & Performance Tweaks is a WordPress plugin that optimizes network requests and fine-tunes WordPress performance settings. It allows you to disable external dependencies like Google Fonts and Google Maps, control content management settings such as post revisions and trash retention, and adjust WordPress behavior for improved performance.

## Features

### Network Optimizations

- **Disable DNS Prefetching** - Removes DNS prefetch to fonts.googleapis.com and s.w.org to reduce external connections
- **Disable Self Pingbacks** - Prevents WordPress from notifying itself when you link to your own posts
- **Disable Google Maps API** - Removes Google Maps scripts loaded by themes or plugins
- **Disable Google Fonts** - Removes Google Fonts loading from external servers

### Content Management

- **Post Revisions Limit** - Set maximum number of revisions to keep per post/page (0-100)
- **Empty Trash Days** - Configure days before permanently deleting trashed items (0-365)
- **Autosave Frequency** - Control how often WordPress automatically saves while editing (10-3600 seconds)
- **Shortcode Cleanup** - Removes leftover shortcodes from deactivated plugins to clean up content display

### Admin Performance

- **Heartbeat Frequency** - Controls how often WordPress checks for updates and notifications in admin (15-300 seconds)

### Data Management

- **Custom Database Table** - Stores all settings in a dedicated table to avoid bloating wp_options
- **Clean Uninstall** - User can choose whether to remove all data on uninstall

## Installation

1. Upload the `network-performance-tweaks` folder to `/wp-content/plugins/`
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Navigate to **Tools → Network & Performance** to configure settings
4. All features are disabled by default - enable the ones you need

## Configuration

Access the plugin settings via **Tools → Network & Performance** in the WordPress admin menu.

### Settings Sections

**Network Optimizations:**
- Toggle individual network optimizations (DNS prefetch, self pingbacks, Google Maps, Google Fonts)

**Content Management:**
- Set numeric limits for revisions, trash retention, and autosave frequency
- Enable shortcode cleanup

**Admin Performance:**
- Adjust WordPress heartbeat frequency

**Uninstall Options:**
- Choose whether to clean up all data on uninstall

## Technical Details

### WordPress APIs Used

- **Plugin API** - Hook system for WordPress modifications
- **Database API** - Secure data storage with prepared statements
- **HTTP API** - Script/style dequeuing for Google services
- **Shortcode API** - Cleanup of non-existent shortcodes
- **Options API** - WordPress constants (WP_POST_REVISIONS, EMPTY_TRASH_DAYS, AUTOSAVE_INTERVAL)

### Security Features

- CSRF protection via WordPress nonces
- Capability checks for admin access (`manage_options`)
- Sanitized and validated input
- Prepared SQL statements with %i placeholder for table names (WordPress 6.2+)
- XSS protection on output
- Error handling for all database operations

### Performance Considerations

- Assets only load on plugin admin page
- Settings cached with lazy loading pattern
- Minimal database queries with proper caching
- No external dependencies
- Object cache integration

## File Structure

```
network-performance-tweaks/
│
├── network-performance-tweaks.php    # Main plugin file, initialization
├── uninstall.php                      # Cleanup on plugin removal
├── README.md                          # This file
├── index.php                          # Security stub
│
├── includes/
│   ├── class-database.php             # Database operations, settings storage
│   ├── class-core.php                 # Core functionality, WordPress hooks
│   ├── class-admin.php                # Admin interface, settings page
│   └── index.php                      # Security stub
│
└── assets/
    ├── admin.css                      # Admin page styling
    └── index.php                      # Security stub
```

### File Descriptions

**network-performance-tweaks.php**
- Plugin header information
- Constant definitions
- File includes and initialization
- Activation and deactivation hooks
- Error handling for class loading

**includes/class-database.php**
- Custom database table creation with error checking
- Settings CRUD operations with prepared statements
- Settings caching for performance (object cache + transients)
- Table cleanup methods
- Full error handling and logging

**includes/class-core.php**
- Implements all performance tweaks
- WordPress hook integrations
- DNS prefetch control
- Google services blocking
- Shortcode cleanup with error handling
- WordPress constants definition with validation
- Lazy loading of settings

**includes/class-admin.php**
- Admin menu registration (under Tools)
- Settings page rendering
- Form handling with full validation
- Asset enqueuing for admin pages
- Comprehensive error feedback

**assets/admin.css**
- Styling for settings page
- Responsive design
- WordPress admin theme consistency

**uninstall.php**
- Checks cleanup preference from database
- Drops custom database table with prepared statements
- Removes transients safely
- Clears object cache
- Full error handling and logging

## Browser Compatibility

- All modern browsers (Chrome, Firefox, Safari, Edge)
- Mobile responsive admin interface
- WordPress 6.2+ compatible

## Requirements

- WordPress 6.2 or higher
- PHP 7.4 or higher
- MySQL 5.0 or higher (MySQL 8.0+ recommended)

## Privacy & Data

This plugin:
- Stores settings locally in WordPress database
- Does not send data to external services
- Does not use cookies or tracking
- GDPR compliant

## Performance Impact

The plugin provides performance improvements by:
- Reducing external HTTP requests
- Limiting database bloat from revisions
- Optimizing autosave and heartbeat frequencies
- Removing unused shortcodes from content

## Changelog

### Version 1.0.1
- **Security**: Fixed all SQL queries to use prepared statements with %i placeholder for table names
- **Performance**: Implemented lazy loading pattern for settings (no longer loaded on every request)
- **Performance**: Settings now cached with object cache integration
- **Security**: Added sanitization for all $_GET and $_POST parameters
- **Security**: Added isset() checks before accessing superglobal arrays
- **Reliability**: Added comprehensive error handling for all database operations
- **Reliability**: Added validation for numeric settings values
- **Reliability**: Added error logging throughout
- **Reliability**: Added table creation verification
- **Code Quality**: Fixed object existence checks before accessing properties
- **Code Quality**: Added deactivation hook
- **Code Quality**: Improved error feedback in admin interface

### Version 1.0.0
- Initial release
- Network optimizations (DNS prefetch, self pingbacks, Google Maps, Google Fonts)
- Content management settings (revisions, trash, autosave, shortcode cleanup)
- Admin performance (heartbeat frequency)
- Custom database table for settings
- Clean uninstall option

---

**Plugin Version:** 1.0.1  
**Requires WordPress:** 6.2+  
**Requires PHP:** 7.4+  
**License:** GPL v2 or later
