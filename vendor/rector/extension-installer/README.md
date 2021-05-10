# Rector Extension Installer
[![Build](https://github.com/rectorphp/rector-installer/workflows/CI/badge.svg)](https://github.com/rectorphp/rector-installer/actions)

Composer plugin for automatic installation of Rector extensions.

## Usage

```bash
composer require --dev rector/rector-installer
```

## Instructions for extension developers

It's best to set the extension's composer package [type](https://getcomposer.org/doc/04-schema.md#type) to `rector-extension` for this plugin to be able to recognize it and to be [discoverable on Packagist](https://packagist.org/explore/?type=rector-extension).

Add `rector` key in the extension `composer.json`'s `extra` section:

```json
{
  "extra": {
    "rector": {
      "includes": [
        "config/config.php"
      ]
    }
  }
}
```

## Limitations

The extension installer depends on Composer script events, therefore you cannot use `--no-scripts` flag.