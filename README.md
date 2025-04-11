# Object Cache Annihilator

A simple file-based object cache implementation for WordPress, designed for testing purposes. This plugin provides a convenient way to test and debug WordPress object cache functionality.

## Features

- File-based object cache implementation
- Easy enable/disable through admin bar
- Cache flushing capabilities
- Support for cache groups and expiration
- Admin interface for cache management

## Requirements

- WordPress 6.2 or higher
- PHP 7.2 or higher
- Write permissions to the `wp-content` directory

## Installation

1. Follow the instructions in the [Distribution](#distribution) section to build the plugin
2. Upload the plugin files to the `/wp-content/plugins/object-cache-annihilator` directory
3. Activate the plugin through the 'Plugins' menu in WordPress

## Usage

Once activated, you'll see a new menu item in the admin bar:

- **Object Cache ☠️**: Main menu item
  - **Die 🔫**: Disable the object cache
  - **Resurrect 👻**: Enable the object cache
  - **Flush 🚽**: Clear all cached data (only available when cache is enabled)

## Development

### Setup

1. Clone the repository
2. Run `composer install` to install dependencies
3. Run `npm install` to install development dependencies

### Development Environment

The plugin includes a WordPress development environment using `@wordpress/env`. To start it:

```bash
npm run env:start
```

Other available commands:
- `npm run env:stop`: Stop the environment
- `npm run env:clean`: Clean the environment
- `npm run env:start:fresh`: Start a fresh environment
- `npm run env:composer`: Run composer commands in the environment

### Code Quality

The plugin uses several tools to maintain code quality:

- PHPCS for coding standards
- PHPStan for static analysis
- Composer for dependency management

Available commands:
- `composer cs`: Run coding standards check
- `composer cs-fix`: Fix coding standards issues
- `composer stan`: Run static analysis

## Distribution

To create a distribution package:

```bash
npm run dist
```

This will:
1. Clean up development files
2. Install production dependencies
3. Create a distribution package in the `dist` directory

## Advanced Usage

### Process-specific Cache Management

For managing the cache within a single PHP process (without affecting the drop-in file):

```php
global $wp_object_cache;

// Enable cache for current process
$wp_object_cache->resurrect();

// Disable cache for current process
$wp_object_cache->die();

// Flush cache for current process
$wp_object_cache->flush();
```

This approach is useful when you need to temporarily enable/disable the cache for specific operations without affecting the global cache state.

## License

This plugin is licensed under the GPL v3 or later.
