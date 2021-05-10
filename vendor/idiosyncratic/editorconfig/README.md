# EditorConfig PHP
PHP implementation of [EditorConfig](https://editorconfig.org)

## Installation

Install with [Composer](https://getcomposer.org):

```
composer require idiosyncratic/editorconfig
```

## Usage

```php
<?php

require_once('vendor/autoload.php');

use Idiosyncratic\EditorConfig\EditorConfig;

$ec = new EditorConfig();

// $config will be an array of the declarations matching for the specified path
$config = $ec->getConfigForPath(__FILE__);

// Print matching configuration rules as string
print $ec->printConfigForPath(__FILE__) . PHP_EOL;
```
