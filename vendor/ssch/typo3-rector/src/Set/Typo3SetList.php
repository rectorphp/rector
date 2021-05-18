<?php

declare (strict_types=1);
namespace Ssch\TYPO3Rector\Set;

use Ssch\TYPO3Rector\Set\Extension\NimutTestingFrameworkSetList;
use Ssch\TYPO3Rector\Set\Extension\SolrSetList;
final class Typo3SetList
{
    /**
     * @var string
     */
    public const TYPO3_76 = __DIR__ . '/../../config/typo3-7.6.php';
    /**
     * @var string
     */
    public const TCA_76 = __DIR__ . '/../../config/v7/tca-76.php';
    /**
     * @var string
     */
    public const TYPO3_87 = __DIR__ . '/../../config/typo3-8.7.php';
    /**
     * @var string
     */
    public const TCA_87 = __DIR__ . '/../../config/v8/tca-87.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_87_EXTENSIONS = __DIR__ . '/../../config/composer/typo3-87-composer-packages-extensions.php';
    /**
     * @var string
     */
    public const TYPO3_95 = __DIR__ . '/../../config/typo3-9.5.php';
    /**
     * @var string
     */
    public const TCA_95 = __DIR__ . '/../../config/v9/tca-95.php';
    /**
     * @var string
     */
    public const TYPOSCRIPT_CONDITIONS_95 = __DIR__ . '/../../config/v9/typoscript-conditions-95.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_95_CORE = __DIR__ . '/../../config/composer/typo3-95-composer-packages-core.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_95_EXTENSIONS = __DIR__ . '/../../config/composer/typo3-95-composer-packages-extensions.php';
    /**
     * @var string
     */
    public const TYPO3_104 = __DIR__ . '/../../config/typo3-10.4.php';
    /**
     * @var string
     */
    public const TCA_104 = __DIR__ . '/../../config/v10/tca-104.php';
    /**
     * @var string
     */
    public const TYPOSCRIPT_CONDITIONS_104 = __DIR__ . '/../../config/v10/typoscript-conditions-104.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_104_CORE = __DIR__ . '/../../config/composer/typo3-104-composer-packages-core.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_104_EXTENSIONS = __DIR__ . '/../../config/composer/typo3-104-composer-packages-extensions.php';
    /**
     * @var string
     */
    public const TYPO3_11 = __DIR__ . '/../../config/typo3-11.0.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_110_CORE = __DIR__ . '/../../config/composer/typo3-110-composer-packages-core.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_TER_TO_PACKAGIST = __DIR__ . '/../../config/composer/move_extension_from_ter_to_packagist.php';
    /**
     * @var string
     */
    public const COMPOSER_PACKAGES_110_EXTENSIONS = __DIR__ . '/../../config/composer/typo3-110-composer-packages-extensions.php';
    /**
     * @var string
     */
    public const DATABASE_TO_DBAL = __DIR__ . '/../../config/database-connection-to-dbal.php';
    /**
     * @var string
     */
    public const UNDERSCORE_TO_NAMESPACE = __DIR__ . '/../../config/underscore-to-namespace.php';
    /**
     * @var string
     * @deprecated Use \Ssch\TYPO3Rector\Set\Extension\NimutTestingFrameworkSetList::NIMUT_TESTING_FRAMEWORK_TO_TYPO3_TESTING_FRAMEWORK
     */
    public const NIMUT_TESTING_FRAMEWORK_TO_TYPO3_TESTING_FRAMEWORK = \Ssch\TYPO3Rector\Set\Extension\NimutTestingFrameworkSetList::NIMUT_TESTING_FRAMEWORK_TO_TYPO3_TESTING_FRAMEWORK;
    /**
     * @var string
     * @deprecated Use \Ssch\TYPO3Rector\Set\Extension\SolrSetList::SOLR_SOLR_PHP_CLIENT_TO_SOLARIUM
     */
    public const SOLR_SOLR_PHP_CLIENT_TO_SOLARIUM = \Ssch\TYPO3Rector\Set\Extension\SolrSetList::SOLR_SOLR_PHP_CLIENT_TO_SOLARIUM;
    /**
     * @var string
     */
    public const EXTBASE_COMMAND_CONTROLLERS_TO_SYMFONY_COMMANDS = __DIR__ . '/../../config/extbase-command-controllers-to-symfony-commands/extbase_command_controllers_to_symfony_commands.php';
}
