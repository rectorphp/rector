<?php

declare(strict_types=1);

namespace Rector\Set\ValueObject;

final class SetList
{
    /**
     * @var string
     */
    public const DEFLUENT = 'defluent';

    /**
     * @var string
     */
    public const ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION = 'action-injection-to-constructor-injection';

    /**
     * @var string
     */
    public const ARRAY_STR_FUNCTIONS_TO_STATIC_CALL = 'array-str-functions-to-static-call';

    /**
     * @var string
     */
    public const CAKEPHP_30 = 'cakephp30';

    /**
     * @var string
     */
    public const CAKEPHP_34 = 'cakephp34';

    /**
     * @var string
     */
    public const CAKEPHP_35 = 'cakephp35';

    /**
     * @var string
     */
    public const CAKEPHP_36 = 'cakephp36';

    /**
     * @var string
     */
    public const CAKEPHP_37 = 'cakephp37';

    /**
     * @var string
     */
    public const CAKEPHP_38 = 'cakephp38';

    /**
     * @var string
     */
    public const CAKEPHP_40 = 'cakephp40';

    /**
     * @var string
     */
    public const CAKEPHP_FLUENT_OPTIONS = 'cakephp-fluent-options';

    /**
     * @var string
     */
    public const CODEIGNITER_40 = 'codeigniter-40';

    /**
     * @var string
     */
    public const CODE_QUALITY = 'code-quality';

    /**
     * @var string
     */
    public const CODING_STYLE = 'coding-style';

    /**
     * @var string
     */
    public const CONSTRUCTOR_INJECTIN_TO_ACTION_INJECTION = 'constructor-injectin-to-action-injection';

    /**
     * @var string
     */
    public const CONTRIBUTTE_TO_SYMFONY = 'contributte-to-symfony';

    /**
     * @var string
     */
    public const DEAD_CLASSES = 'dead-classes';

    /**
     * @var string
     */
    public const DEAD_CODE = 'dead-code';

    /**
     * @var string
     */
    public const DOCTRINE_25 = 'doctrine25';

    /**
     * @var string
     */
    public const DOCTRINE_BEHAVIORS_20 = 'doctrine-behaviors-20';

    /**
     * @var string
     */
    public const DOCTRINE_CODE_QUALITY = 'doctrine-code-quality';

    /**
     * @var string
     */
    public const DOCTRINE_COMMON_20 = 'doctrine-common-20';

    /**
     * @var string
     */
    public const DOCTRINE_DBAL_210 = 'doctrine-dbal-210';

    /**
     * @var string
     */
    public const DOCTRINE_DBAL_30 = 'doctrine-dbal-30';

    /**
     * @var string
     */
    public const DOCTRINE_GEDMO_TO_KNPLABS = 'doctrine-gedmo-to-knplabs';

    /**
     * @var string
     */
    public const DOCTRINE_ID_TO_UUID_STEP_1 = 'doctrine-id-to-uuid-step-1';

    /**
     * @var string
     */
    public const DOCTRINE_ID_TO_UUID_STEP_2 = 'doctrine-id-to-uuid-step-2';

    /**
     * @var string
     */
    public const DOCTRINE_ID_TO_UUID_STEP_3 = 'doctrine-id-to-uuid-step-3';

    /**
     * @var string
     */
    public const DOCTRINE_ID_TO_UUID_STEP_4 = 'doctrine-id-to-uuid-step-4';

    /**
     * @var string
     */
    public const DOCTRINE_ID_TO_UUID_STEP_5 = 'doctrine-id-to-uuid-step-5';

    /**
     * @var string
     */
    public const DOCTRINE_REPOSITORY_AS_SERVICE = 'doctrine-repository-as-service';

    /**
     * @var string
     */
    public const DOCTRINE_SERVICES = 'doctrine-services';

    /**
     * @var string
     */
    public const DOWNGRADE = 'downgrade';

    /**
     * @var string
     */
    public const EASY_ADMIN_BUNDLE_20 = 'easy-admin-bundle20';

    /**
     * @var string
     */
    public const ELASTICSEARCH_DSL_50 = 'elasticsearch-dsl50';

    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_BUNDLE_40 = 'framework-extra-bundle-40';

    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_BUNDLE_50 = 'framework-extra-bundle-50';

    /**
     * @var string
     */
    public const GMAGICK_TO_IMAGICK = 'gmagick_to_imagick';

    /**
     * @var string
     */
    public const GUZZLE_50 = 'guzzle50';

    /**
     * @var string
     */
    public const JMS_DECOUPLE = 'jms-decouple';

    /**
     * @var string
     */
    public const KDYBY_EVENTS_TO_CONTRIBUTTE_EVENT_DISPATCHER = 'kdyby-events-to-contributte-event-dispatcher';

    /**
     * @var string
     */
    public const KDYBY_TO_SYMFONY = 'kdyby-to-symfony';

    /**
     * @var string
     */
    public const KDYBY_TRANSLATOR_TO_CONTRIBUTTE_TRANSLATION = 'kdyby-translator-to-contributte-translation';

    /**
     * @var string
     */
    public const LARAVEL_50 = 'laravel50';

    /**
     * @var string
     */
    public const LARAVEL_51 = 'laravel51';

    /**
     * @var string
     */
    public const LARAVEL_52 = 'laravel52';

    /**
     * @var string
     */
    public const LARAVEL_53 = 'laravel53';

    /**
     * @var string
     */
    public const LARAVEL_54 = 'laravel54';

    /**
     * @var string
     */
    public const LARAVEL_55 = 'laravel55';

    /**
     * @var string
     */
    public const LARAVEL_56 = 'laravel56';

    /**
     * @var string
     */
    public const LARAVEL_57 = 'laravel57';

    /**
     * @var string
     */
    public const LARAVEL_58 = 'laravel58';

    /**
     * @var string
     */
    public const LARAVEL_60 = 'laravel60';

    /**
     * @var string
     */
    public const LARAVEL_STATIC_TO_INJECTION = 'laravel-static-to-injection';

    /**
     * @var string
     */
    public const MOCKISTA_TO_MOCKERY = 'mockista-to-mockery';

    /**
     * @var string
     */
    public const MONOLOG_20 = 'monolog20';

    /**
     * @var string
     */
    public const MYSQL_TO_MYSQLI = 'mysql-to-mysqli';

    /**
     * @var string
     */
    public const NAMING = 'naming';

    /**
     * @var string
     */
    public const NETTE_30 = 'nette-30';

    /**
     * @var string
     */
    public const NETTE_CODE_QUALITY = 'nette-code-quality';

    /**
     * @var string
     */
    public const NETTE_CONTROL_TO_SYMFONY_CONTROLLER = 'nette-control-to-symfony-controller';

    /**
     * @var string
     */
    public const NETTE_FORMS_TO_SYMFONY = 'nette-forms-to-symfony';

    /**
     * @var string
     */
    public const NETTE_PARAM_TYPES = 'nette-param-types';

    /**
     * @var string
     */
    public const NETTE_RETURN_TYPES = 'nette-return-types';

    /**
     * @var string
     */
    public const NETTE_TESTER_TO_PHPUNIT = 'nette-tester-to-phpunit';

    /**
     * @var string
     */
    public const NETTE_TO_SYMFONY = 'nette-to-symfony';

    /**
     * @var string
     */
    public const NETTE_UTILS_CODE_QUALITY = 'nette-utils-code-quality';

    /**
     * @var string
     */
    public const ORDER = 'order';

    /**
     * @var string
     */
    public const PERFORMANCE = 'performance';

    /**
     * @var string
     */
    public const PHALCON_40 = 'phalcon40';

    /**
     * @var string
     */
    public const PHPEXCEL_TO_PHPSPREADSHEET = 'phpexcel-to-phpspreadsheet';

    /**
     * @var string
     */
    public const PHPSPEC_30 = 'phpspec30';

    /**
     * @var string
     */
    public const PHPSPEC_40 = 'phpspec40';

    /**
     * @var string
     */
    public const PHPSPEC_TO_PHPUNIT = 'phpspec-to-phpunit';

    /**
     * @var string
     */
    public const PHPSTAN = 'phpstan';

    /**
     * @var string
     */
    public const PHPUNIT80_DMS = 'phpunit80-dms';

    /**
     * @var string
     */
    public const PHPUNIT_40 = 'phpunit40';

    /**
     * @var string
     */
    public const PHPUNIT_50 = 'phpunit50';

    /**
     * @var string
     */
    public const PHPUNIT_60 = 'phpunit60';

    /**
     * @var string
     */
    public const PHPUNIT_70 = 'phpunit70';

    /**
     * @var string
     */
    public const PHPUNIT_75 = 'phpunit75';

    /**
     * @var string
     */
    public const PHPUNIT_80 = 'phpunit80';

    /**
     * @var string
     */
    public const PHPUNIT_90 = 'phpunit90';

    /**
     * @var string
     */
    public const PHPUNIT_91 = 'phpunit91';

    /**
     * @var string
     */
    public const PHPUNIT_CODE_QUALITY = 'phpunit-code-quality';

    /**
     * @var string
     */
    public const PHPUNIT_EXCEPTION = 'phpunit-exception';

    /**
     * @var string
     */
    public const PHPUNIT_INJECTOR = 'phpunit-injector';

    /**
     * @var string
     */
    public const PHPUNIT_MOCK = 'phpunit-mock';

    /**
     * @var string
     */
    public const PHPUNIT_SPECIFIC_METHOD = 'phpunit-specific-method';

    /**
     * @var string
     */
    public const PHPUNIT_YIELD_DATA_PROVIDER = 'phpunit-yield-data-provider';

    /**
     * @var string
     */
    public const PHP_52 = 'php52';

    /**
     * @var string
     */
    public const PHP_53 = 'php53';

    /**
     * @var string
     */
    public const PHP_54 = 'php54';

    /**
     * @var string
     */
    public const PHP_55 = 'php55';

    /**
     * @var string
     */
    public const PHP_56 = 'php56';

    /**
     * @var string
     */
    public const PHP_70 = 'php70';

    /**
     * @var string
     */
    public const PHP_71 = 'php71';

    /**
     * @var string
     */
    public const PHP_72 = 'php72';

    /**
     * @var string
     */
    public const PHP_73 = 'php73';

    /**
     * @var string
     */
    public const PHP_74 = 'php74';

    /**
     * @var string
     */
    public const PHP_80 = 'php80';

    /**
     * @var string
     */
    public const PHP_CODE_SNIFFER_30 = 'php-code-sniffer30';

    /**
     * @var string
     */
    public const PHP_DI_DECOUPLE = 'php-di-decouple';

    /**
     * @var string
     */
    public const PRIVATIZATION = 'privatization';

    /**
     * @var string
     */
    public const PSR_4 = 'psr-4';

    /**
     * @var string
     */
    public const SAFE_07 = 'safe07';

    /**
     * @var string
     */
    public const SHOPWARE_55 = 'shopware55';

    /**
     * @var string
     */
    public const SHOPWARE_56 = 'shopware56';

    /**
     * @var string
     */
    public const SOLID = 'solid';

    /**
     * @var string
     */
    public const SWIFTMAILER_60 = 'swiftmailer60';

    /**
     * @var string
     */
    public const SYLIUS_10 = 'sylius10';

    /**
     * @var string
     */
    public const SYLIUS_102 = 'sylius102';

    /**
     * @var string
     */
    public const SYLIUS_109 = 'sylius109';

    /**
     * @var string
     */
    public const SYMFONY_50_TYPES = 'symfony50-types';

    /**
     * @var string
     */
    public const SYMFONY_26 = 'symfony26';

    /**
     * @var string
     */
    public const SYMFONY_28 = 'symfony28';

    /**
     * @var string
     */
    public const SYMFONY_30 = 'symfony30';

    /**
     * @var string
     */
    public const SYMFONY_31 = 'symfony31';

    /**
     * @var string
     */
    public const SYMFONY_32 = 'symfony32';

    /**
     * @var string
     */
    public const SYMFONY_33 = 'symfony33';

    /**
     * @var string
     */
    public const SYMFONY_34 = 'symfony34';

    /**
     * @var string
     */
    public const SYMFONY_40 = 'symfony40';

    /**
     * @var string
     */
    public const SYMFONY_41 = 'symfony41';

    /**
     * @var string
     */
    public const SYMFONY_42 = 'symfony42';

    /**
     * @var string
     */
    public const SYMFONY_43 = 'symfony43';

    /**
     * @var string
     */
    public const SYMFONY_44 = 'symfony44';

    /**
     * @var string
     */
    public const SYMFONY_50 = 'symfony50';

    /**
     * @var string
     */
    public const SYMFONY_CODE_QUALITY = 'symfony-code-quality';

    /**
     * @var string
     */
    public const SYMFONY_CONSTRUCTOR_INJECTION = 'symfony-constructor-injection';

    /**
     * @var string
     */
    public const SYMFONY_PHPUNIT = 'symfony-phpunit';

    /**
     * @var string
     */
    public const TWIG_112 = 'twig112';

    /**
     * @var string
     */
    public const TWIG_127 = 'twig127';

    /**
     * @var string
     */
    public const TWIG_134 = 'twig134';

    /**
     * @var string
     */
    public const TWIG_140 = 'twig140';

    /**
     * @var string
     */
    public const TWIG_20 = 'twig20';

    /**
     * @var string
     */
    public const TWIG_240 = 'twig240';

    /**
     * @var string
     */
    public const TWIG_UNDERSCORE_TO_NAMESPACE = 'twig-underscore-to-namespace';

    /**
     * @var string
     */
    public const TYPE_DECLARATION = 'type-declaration';

    /**
     * @var string
     */
    public const UNWRAP_COMPAT = 'unwrap-compat';

    /**
     * @var string
     */
    public const SYMFONY_PHP_CONFIG = 'symfony-php-config';
}
