<?php

declare(strict_types=1);

namespace Rector\Set\ValueObject;

use Rector\Laravel\Set\LaravelSetList;
use Rector\Set\Contract\SetListInterface;
use Rector\Set\NetteSetList;
use Rector\Set\SymfonySetList;

final class SetList implements SetListInterface
{
    /**
     * @var string
     */
    public const DEFLUENT = __DIR__ . '/../../../config/set/defluent.php';

    /**
     * @var string
     */
    public const ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION = __DIR__ . '/../../../config/set/action-injection-to-constructor-injection.php';

    /**
     * @var string
     */
    public const CAKEPHP_30 = __DIR__ . '/../../../config/set/cakephp30.php';

    /**
     * @var string
     */
    public const CAKEPHP_34 = __DIR__ . '/../../../config/set/cakephp34.php';

    /**
     * @var string
     */
    public const CAKEPHP_35 = __DIR__ . '/../../../config/set/cakephp35.php';

    /**
     * @var string
     */
    public const CAKEPHP_36 = __DIR__ . '/../../../config/set/cakephp36.php';

    /**
     * @var string
     */
    public const CAKEPHP_37 = __DIR__ . '/../../../config/set/cakephp37.php';

    /**
     * @var string
     */
    public const CAKEPHP_38 = __DIR__ . '/../../../config/set/cakephp38.php';

    /**
     * @var string
     */
    public const CAKEPHP_40 = __DIR__ . '/../../../config/set/cakephp40.php';

    /**
     * @var string
     */
    public const CAKEPHP_41 = __DIR__ . '/../../../config/set/cakephp41.php';

    /**
     * @var string
     */
    public const CAKEPHP_42 = __DIR__ . '/../../../config/set/cakephp42.php';

    /**
     * @var string
     */
    public const CAKEPHP_FLUENT_OPTIONS = __DIR__ . '/../../../config/set/cakephp-fluent-options.php';

    /**
     * @var string
     */
    public const CODE_QUALITY = __DIR__ . '/../../../config/set/code-quality.php';

    /**
     * @var string
     */
    public const CODE_QUALITY_STRICT = __DIR__ . '/../../../config/set/code-quality-strict.php';

    /**
     * @var string
     */
    public const CODING_STYLE = __DIR__ . '/../../../config/set/coding-style.php';

    /**
     * @var string
     */
    public const CONTRIBUTTE_TO_SYMFONY = __DIR__ . '/../../../config/set/contributte-to-symfony.php';

    /**
     * @var string
     */
    public const DEAD_CODE = __DIR__ . '/../../../config/set/dead-code.php';

    /**
     * @var string
     */
    public const DOCTRINE_25 = __DIR__ . '/../../../config/set/doctrine25.php';

    /**
     * @var string
     */
    public const DOCTRINE_BEHAVIORS_20 = __DIR__ . '/../../../config/set/doctrine-behaviors-20.php';

    /**
     * @var string
     */
    public const DOCTRINE_CODE_QUALITY = __DIR__ . '/../../../config/set/doctrine-code-quality.php';

    /**
     * @var string
     */
    public const DOCTRINE_COMMON_20 = __DIR__ . '/../../../config/set/doctrine-common-20.php';

    /**
     * @var string
     */
    public const DOCTRINE_DBAL_210 = __DIR__ . '/../../../config/set/doctrine-dbal-210.php';

    /**
     * @var string
     */
    public const DOCTRINE_DBAL_211 = __DIR__ . '/../../../config/set/doctrine-dbal-211.php';

    /**
     * @var string
     */
    public const DOCTRINE_DBAL_30 = __DIR__ . '/../../../config/set/doctrine-dbal-30.php';

    /**
     * @var string
     */
    public const DOCTRINE_GEDMO_TO_KNPLABS = __DIR__ . '/../../../config/set/doctrine-gedmo-to-knplabs.php';

    /**
     * @var string
     */
    public const DOCTRINE_REPOSITORY_AS_SERVICE = __DIR__ . '/../../../config/set/doctrine-repository-as-service.php';

    /**
     * @var string
     */
    public const FLYSYSTEM_20 = __DIR__ . '/../../../config/set/flysystem-20.php';

    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_BUNDLE_40 = __DIR__ . '/../../../config/set/framework-extra-bundle-40.php';

    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_BUNDLE_50 = __DIR__ . '/../../../config/set/framework-extra-bundle-50.php';

    /**
     * @var string
     */
    public const GMAGICK_TO_IMAGICK = __DIR__ . '/../../../config/set/gmagick_to_imagick.php';

    /**
     * @var string
     */
    public const KDYBY_EVENTS_TO_CONTRIBUTTE_EVENT_DISPATCHER = __DIR__ . '/../../../config/set/kdyby-events-to-contributte-event-dispatcher.php';

    /**
     * @var string
     */
    public const KDYBY_TO_SYMFONY = __DIR__ . '/../../../config/set/kdyby-to-symfony.php';

    /**
     * @var string
     */
    public const KDYBY_TRANSLATOR_TO_CONTRIBUTTE_TRANSLATION = __DIR__ . '/../../../config/set/kdyby-translator-to-contributte-translation.php';

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const ARRAY_STR_FUNCTIONS_TO_STATIC_CALL = LaravelSetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_50 = LaravelSetList::LARAVEL_50;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_51 = LaravelSetList::LARAVEL_51;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_52 = LaravelSetList::LARAVEL_52;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_53 = LaravelSetList::LARAVEL_53;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_54 = LaravelSetList::LARAVEL_54;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_55 = LaravelSetList::LARAVEL_55;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_56 = LaravelSetList::LARAVEL_56;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_57 = LaravelSetList::LARAVEL_57;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_58 = LaravelSetList::LARAVEL_58;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_60 = LaravelSetList::LARAVEL_60;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_STATIC_TO_INJECTION = LaravelSetList::LARAVEL_STATIC_TO_INJECTION;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_CODE_QUALITY = LaravelSetList::LARAVEL_CODE_QUALITY;

    /**
     * For BC layer
     * @deprecated Use LaravelSetList from rector/rector-laravel instead
     * @var string
     */
    public const LARAVEL_ARRAY_STR_FUNCTION_TO_STATIC_CALL = LaravelSetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL;

    /**
     * @var string
     */
    public const MONOLOG_20 = __DIR__ . '/../../../config/set/monolog20.php';

    /**
     * @var string
     */
    public const MYSQL_TO_MYSQLI = __DIR__ . '/../../../config/set/mysql-to-mysqli.php';

    /**
     * @var string
     */
    public const NAMING = __DIR__ . '/../../../config/set/naming.php';

    /**
     * @var string
     */
    public const NETTE_24 = NetteSetList::NETTE_24;

    /**
     * @var string
     */
    public const NETTE_30 = NetteSetList::NETTE_30;

    /**
     * @var string
     */
    public const NETTE_31 = NetteSetList::NETTE_31;

    /**
     * @var string
     */
    public const NETTE_CODE_QUALITY = NetteSetList::NETTE_CODE_QUALITY;

    /**
     * @var string
     */
    public const NETTE_UTILS_CODE_QUALITY = NetteSetList::NETTE_UTILS_CODE_QUALITY;

    /**
     * @var string
     */
    public const NETTE_CONTROL_TO_SYMFONY_CONTROLLER = __DIR__ . '/../../../config/set/nette-control-to-symfony-controller.php';

    /**
     * @var string
     */
    public const NETTE_FORMS_TO_SYMFONY = __DIR__ . '/../../../config/set/nette-forms-to-symfony.php';

    /**
     * @var string
     */
    public const NETTE_TESTER_TO_PHPUNIT = __DIR__ . '/../../../config/set/nette-tester-to-phpunit.php';

    /**
     * @var string
     */
    public const NETTE_TO_SYMFONY = __DIR__ . '/../../../config/set/nette-to-symfony.php';

    /**
     * @var string
     */
    public const ORDER = __DIR__ . '/../../../config/set/order.php';

    /**
     * @var string
     */
    public const PHALCON_40 = __DIR__ . '/../../../config/set/phalcon40.php';

    /**
     * @var string
     */
    public const PHPEXCEL_TO_PHPSPREADSHEET = __DIR__ . '/../../../config/set/phpexcel-to-phpspreadsheet.php';

    /**
     * @var string
     */
    public const PHPSPEC_30 = __DIR__ . '/../../../config/set/phpspec30.php';

    /**
     * @var string
     */
    public const PHPSPEC_40 = __DIR__ . '/../../../config/set/phpspec40.php';

    /**
     * @var string
     */
    public const PHPSPEC_TO_PHPUNIT = __DIR__ . '/../../../config/set/phpspec-to-phpunit.php';

    /**
     * @var string
     */
    public const PHPUNIT80_DMS = __DIR__ . '/../../../config/set/phpunit80-dms.php';

    /**
     * @var string
     */
    public const PHPUNIT_40 = __DIR__ . '/../../../config/set/phpunit40.php';

    /**
     * @var string
     */
    public const PHPUNIT_50 = __DIR__ . '/../../../config/set/phpunit50.php';

    /**
     * @var string
     */
    public const PHPUNIT_60 = __DIR__ . '/../../../config/set/phpunit60.php';

    /**
     * @var string
     */
    public const PHPUNIT_70 = __DIR__ . '/../../../config/set/phpunit70.php';

    /**
     * @var string
     */
    public const PHPUNIT_75 = __DIR__ . '/../../../config/set/phpunit75.php';

    /**
     * @var string
     */
    public const PHPUNIT_80 = __DIR__ . '/../../../config/set/phpunit80.php';

    /**
     * @var string
     */
    public const PHPUNIT_90 = __DIR__ . '/../../../config/set/phpunit90.php';

    /**
     * @var string
     */
    public const PHPUNIT_91 = __DIR__ . '/../../../config/set/phpunit91.php';

    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = __DIR__ . '/../../../config/set/phpunit-code-quality.php';

    /**
     * @var string
     */
    public const PHPUNIT_EXCEPTION = __DIR__ . '/../../../config/set/phpunit-exception.php';

    /**
     * @var string
     */
    public const PHPUNIT_MOCK = __DIR__ . '/../../../config/set/phpunit-mock.php';

    /**
     * @var string
     */
    public const PHPUNIT_SPECIFIC_METHOD = __DIR__ . '/../../../config/set/phpunit-specific-method.php';

    /**
     * @var string
     */
    public const PHPUNIT_YIELD_DATA_PROVIDER = __DIR__ . '/../../../config/set/phpunit-yield-data-provider.php';

    /**
     * @var string
     */
    public const PHP_52 = __DIR__ . '/../../../config/set/php52.php';

    /**
     * @var string
     */
    public const PHP_53 = __DIR__ . '/../../../config/set/php53.php';

    /**
     * @var string
     */
    public const PHP_54 = __DIR__ . '/../../../config/set/php54.php';

    /**
     * @var string
     */
    public const PHP_55 = __DIR__ . '/../../../config/set/php55.php';

    /**
     * @var string
     */
    public const PHP_56 = __DIR__ . '/../../../config/set/php56.php';

    /**
     * @var string
     */
    public const PHP_70 = __DIR__ . '/../../../config/set/php70.php';

    /**
     * @var string
     */
    public const PHP_71 = __DIR__ . '/../../../config/set/php71.php';

    /**
     * @var string
     */
    public const PHP_72 = __DIR__ . '/../../../config/set/php72.php';

    /**
     * @var string
     */
    public const PHP_73 = __DIR__ . '/../../../config/set/php73.php';

    /**
     * @var string
     */
    public const PHP_74 = __DIR__ . '/../../../config/set/php74.php';

    /**
     * @var string
     */
    public const PHP_80 = __DIR__ . '/../../../config/set/php80.php';

    /**
     * @var string
     */
    public const PRIVATIZATION = __DIR__ . '/../../../config/set/privatization.php';

    /**
     * @var string
     */
    public const PSR_4 = __DIR__ . '/../../../config/set/psr-4.php';

    /**
     * @var string
     */
    public const SAFE_07 = __DIR__ . '/../../../config/set/safe07.php';

    /**
     * @var string
     */
    public const SWIFTMAILER_60 = __DIR__ . '/../../../config/set/swiftmailer60.php';

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_26 = SymfonySetList::SYMFONY_26;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_28 = SymfonySetList::SYMFONY_28;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_30 = SymfonySetList::SYMFONY_30;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_31 = SymfonySetList::SYMFONY_31;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_32 = SymfonySetList::SYMFONY_32;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_33 = SymfonySetList::SYMFONY_33;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_34 = SymfonySetList::SYMFONY_34;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_40 = SymfonySetList::SYMFONY_40;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_41 = SymfonySetList::SYMFONY_41;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_42 = SymfonySetList::SYMFONY_42;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_43 = SymfonySetList::SYMFONY_43;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_44 = SymfonySetList::SYMFONY_44;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_50 = SymfonySetList::SYMFONY_50;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_50_TYPES = SymfonySetList::SYMFONY_50_TYPES;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_52 = SymfonySetList::SYMFONY_52;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_CODE_QUALITY = SymfonySetList::SYMFONY_CODE_QUALITY;

    /**
     * For BC layer
     * @deprecated Use SymfonySetList from rector/rector-symfony instead
     * @var string
     */
    public const SYMFONY_CONSTRUCTOR_INJECTION = SymfonySetList::SYMFONY_CONSTRUCTOR_INJECTION;

    /**
     * @var string
     */
    public const TWIG_112 = __DIR__ . '/../../../config/set/twig112.php';

    /**
     * @var string
     */
    public const TWIG_127 = __DIR__ . '/../../../config/set/twig127.php';

    /**
     * @var string
     */
    public const TWIG_134 = __DIR__ . '/../../../config/set/twig134.php';

    /**
     * @var string
     */
    public const TWIG_140 = __DIR__ . '/../../../config/set/twig140.php';

    /**
     * @var string
     */
    public const TWIG_20 = __DIR__ . '/../../../config/set/twig20.php';

    /**
     * @var string
     */
    public const TWIG_240 = __DIR__ . '/../../../config/set/twig240.php';

    /**
     * @var string
     */
    public const TWIG_UNDERSCORE_TO_NAMESPACE = __DIR__ . '/../../../config/set/twig-underscore-to-namespace.php';

    /**
     * @var string
     */
    public const TYPE_DECLARATION = __DIR__ . '/../../../config/set/type-declaration.php';

    /**
     * @var string
     */
    public const TYPE_DECLARATION_STRICT = __DIR__ . '/../../../config/set/type-declaration-strict.php';

    /**
     * @var string
     */
    public const UNWRAP_COMPAT = __DIR__ . '/../../../config/set/unwrap-compat.php';

    /**
     * @var string
     */
    public const EARLY_RETURN = __DIR__ . '/../../../config/set/early-return.php';

    /**
     * @var string
     */
    public const CARBON_2 = __DIR__ . '/../../../config/set/carbon-2.php';
}
