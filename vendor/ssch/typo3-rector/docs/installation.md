# Installation

Install the library.

```bash
$ composer require --dev ssch/typo3-rector
```

## Composer conflicts

It is not uncommon to run into unresolvable composer conflicts when installing typo3-rector, especially with older TYPO3 Versions (< 9.5 LTS), for example TYPO3 8.7 LTS.

The best solution is to install the package [ssch/typo3-rector-prefixed](https://github.com/sabbelasichon/typo3-rector-prefixed)

```bash
$ composer require ssch/typo3-rector-prefixed --dev
```

## Non composer installations

If you have a non composer TYPO3 installation. Don´t worry.
Install typo3-rector either as a global dependency:

```bash
$ composer global require --dev ssch/typo3-rector
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

Afterwards run rector:

```bash
php ~/.composer/vendor/bin/rector process public/typo3conf/ext/your_extension/  -c .rector/config.php -n --autoload-file autoload.php
```

Note that the path to the rector executable can vary on your system.
