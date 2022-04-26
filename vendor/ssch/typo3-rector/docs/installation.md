## Table of Contents
1. [Examples in action](./examples_in_action.md)
1. [Overview of all rules](./all_rectors_overview.md)
1. [Installation](./installation.md)
1. [Configuration and Processing](./configuration_and_processing.md)
1. [Best practice guide](./best_practice_guide.md)
1. [Special rules](./special_rules.md)
1. [Beyond PHP - Entering the realm of FileProcessors](./beyond_php_file_processors.md)
1. [Limitations](./limitations.md)
1. [Contribution](./contribution.md)

# Installation

This repository (`ssch/typo3-rector`) is for development TYPO3 Rector only.
Head to [`rectorphp/rector`](http://github.com/rectorphp/rector) for installation.

[`rectorphp/rector`](http://github.com/rectorphp/rector) is a prefixed and downgraded version (down to PHP 7.1) to use it in older projects and to avoid dependency conflicts.

```bash
$ composer require --dev rector/rector
```

## Non composer installations

If you have a non composer TYPO3 installation. Don´t worry.
Install typo3-rector either as a global dependency:

```bash
$ composer global require --dev rector/rector
```

Add an extra autoload file. In the example case it is placed in the Document Root of your TYPO3 project.
The autoload.php should look something like that for TYPO3 Version 9:

```php
<?php
use TYPO3\CMS\Core\Core\Bootstrap;
define('PATH_site', __DIR__.'/');
$classLoader = require PATH_site .'/typo3_src/vendor/autoload.php';

Bootstrap::getInstance()
         ->initializeClassLoader($classLoader)
         ->setRequestType(TYPO3_REQUESTTYPE_CLI)
         ->baseSetup();
```

For TYPO3 version 10 the autoload.php should look like this:

```php
<?php
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Package\UnitTestPackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

define('PATH_site', rtrim(strtr(getcwd(), '\\', '/'), '/').'/');
define('PATH_thisScript', PATH_site.'typo3/index.php');
$_SERVER['SCRIPT_NAME'] = PATH_thisScript;
putenv('TYPO3_PATH_ROOT='.getcwd());
define('TYPO3_MODE', 'BE');
define('TYPO3_PATH_PACKAGES', __DIR__.'/typo3_src/vendor/');

$classLoaderFilepath = TYPO3_PATH_PACKAGES.'autoload.php';

$classLoader = require $classLoaderFilepath;

$requestType = SystemEnvironmentBuilder::REQUESTTYPE_BE | SystemEnvironmentBuilder::REQUESTTYPE_CLI;
SystemEnvironmentBuilder::run(0, $requestType);

Bootstrap::initializeClassLoader($classLoader);
Bootstrap::baseSetup();

// Initialize default TYPO3_CONF_VARS
$configurationManager = new ConfigurationManager();
$GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

$cache = new PhpFrontend('core', new NullBackend('production', []));
// Set all packages to active
$packageManager = Bootstrap::createPackageManager(UnitTestPackageManager::class, $cache);

GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
ExtensionManagementUtility::setPackageManager($packageManager);

ClassLoadingInformation::dumpClassLoadingInformation();
ClassLoadingInformation::registerClassLoadingInformation();
```
For TYPO3 version 11 the autoload.php should look like this:

```php
<?php
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Frontend\PhpFrontend;
use TYPO3\CMS\Core\Configuration\ConfigurationManager;
use TYPO3\CMS\Core\Core\Bootstrap;
use TYPO3\CMS\Core\Core\ClassLoadingInformation;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Package\PackageManager;
use TYPO3\CMS\Core\Package\UnitTestPackageManager;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Package\Cache\PackageStatesPackageCache;
use TYPO3\CMS\Core\Core\Environment;

define('PATH_site', rtrim(strtr(getcwd(), '\\', '/'), '/').'/');
define('PATH_thisScript', PATH_site.'typo3/index.php');
$_SERVER['SCRIPT_NAME'] = PATH_thisScript;
putenv('TYPO3_PATH_ROOT='.getcwd());
define('TYPO3_MODE', 'BE');
define('TYPO3_PATH_PACKAGES', __DIR__.'/typo3_src/vendor/');

$classLoaderFilepath = TYPO3_PATH_PACKAGES.'autoload.php';

$classLoader = require $classLoaderFilepath;

$requestType = SystemEnvironmentBuilder::REQUESTTYPE_BE | SystemEnvironmentBuilder::REQUESTTYPE_CLI;
SystemEnvironmentBuilder::run(0, $requestType);

Bootstrap::initializeClassLoader($classLoader);
Bootstrap::baseSetup();

// Initialize default TYPO3_CONF_VARS
$configurationManager = new ConfigurationManager();
$GLOBALS['TYPO3_CONF_VARS'] = $configurationManager->getDefaultConfiguration();

$cache = new PhpFrontend('core', new NullBackend('production', []));
$packageStateCache = new PackageStatesPackageCache(Environment::getLegacyConfigPath() . '/PackageStates.php', $cache);  
// Set all packages to active
$packageManager = Bootstrap::createPackageManager(UnitTestPackageManager::class, $packageStateCache);

GeneralUtility::setSingletonInstance(PackageManager::class, $packageManager);
ExtensionManagementUtility::setPackageManager($packageManager);

ClassLoadingInformation::dumpClassLoadingInformation();
ClassLoadingInformation::registerClassLoadingInformation();
```
Afterwards run rector:

```bash
php ~/.composer/vendor/bin/rector process public/typo3conf/ext/your_extension/  -c .rector/config.php -n --autoload-file=autoload.php
```

Note that the path to the rector executable can vary on your system.
