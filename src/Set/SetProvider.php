<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Rector\Core\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetProvider
{
    /**
     * @var array<string, string>
     */
    private const SET_PATH_TO_FILEPATH = [
        Set::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION => __DIR__ . '/../../config/set/action-injection-to-constructor-injection.php',
        Set::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL => __DIR__ . '/../../config/set/laravel-array-str-functions-to-static-call.php',
        Set::CAKEPHP_30 => __DIR__ . '/../../config/set/cakephp30.php',
        Set::CAKEPHP_34 => __DIR__ . '/../../config/set/cakephp34.php',
        Set::CAKEPHP_35 => __DIR__ . '/../../config/set/cakephp35.php',
        Set::CAKEPHP_36 => __DIR__ . '/../../config/set/cakephp36.php',
        Set::CAKEPHP_37 => __DIR__ . '/../../config/set/cakephp37.php',
        Set::CAKEPHP_38 => __DIR__ . '/../../config/set/cakephp38.php',
        Set::CAKEPHP_40 => __DIR__ . '/../../config/set/cakephp40.php',
        Set::CAKEPHP_FLUENT_OPTIONS => __DIR__ . '/../../config/set/cakephp-fluent-options.php',
        Set::CELEBRITY => __DIR__ . '/../../config/set/celebrity.php',
        Set::CODEIGNITER_40 => __DIR__ . '/../../config/set/codeigniter-40.php',
        Set::CODE_QUALITY => __DIR__ . '/../../config/set/code-quality.php',
        Set::CODING_STYLE => __DIR__ . '/../../config/set/coding-style.php',
        Set::CONSTRUCTOR_INJECTIN_TO_ACTION_INJECTION => __DIR__ . '/../../config/set/constructor-injectin-to-action-injection.php',
        Set::CONTRIBUTTE_TO_SYMFONY => __DIR__ . '/../../config/set/contributte-to-symfony.php',
        Set::DEAD_CLASSES => __DIR__ . '/../../config/set/dead-classes.php',
        Set::DEAD_CODE => __DIR__ . '/../../config/set/dead-code.php',
        Set::DOCTRINE_25 => __DIR__ . '/../../config/set/doctrine25.php',
        Set::DOCTRINE_BEHAVIORS_20 => __DIR__ . '/../../config/set/doctrine-behaviors-20.php',
        Set::DOCTRINE_CODE_QUALITY => __DIR__ . '/../../config/set/doctrine-code-quality.php',
        Set::DOCTRINE_COMMON_20 => __DIR__ . '/../../config/set/doctrine-common-20.php',
        Set::DOCTRINE_DBAL_210 => __DIR__ . '/../../config/set/doctrine-dbal-210.php',
        Set::DOCTRINE_DBAL_30 => __DIR__ . '/../../config/set/doctrine-dbal-30.php',
        Set::DOCTRINE_GEDMO_TO_KNPLABS => __DIR__ . '/../../config/set/doctrine-gedmo-to-knplabs.php',
        Set::DOCTRINE_ID_TO_UUID_STEP_1 => __DIR__ . '/../../config/set/doctrine-id-to-uuid-step-1.php',
        Set::DOCTRINE_ID_TO_UUID_STEP_2 => __DIR__ . '/../../config/set/doctrine-id-to-uuid-step-2.php',
        Set::DOCTRINE_ID_TO_UUID_STEP_3 => __DIR__ . '/../../config/set/doctrine-id-to-uuid-step-3.php',
        Set::DOCTRINE_ID_TO_UUID_STEP_4 => __DIR__ . '/../../config/set/doctrine-id-to-uuid-step-4.php',
        Set::DOCTRINE_ID_TO_UUID_STEP_5 => __DIR__ . '/../../config/set/doctrine-id-to-uuid-step-5.php',
        Set::DOCTRINE_REPOSITORY_AS_SERVICE => __DIR__ . '/../../config/set/doctrine-repository-as-service.php',
        Set::DOCTRINE_SERVICES => __DIR__ . '/../../config/set/doctrine-services.php',
        Set::DOWNGRADE => __DIR__ . '/../../config/set/downgrade.php',
        Set::EASY_ADMIN_BUNDLE_20 => __DIR__ . '/../../config/set/easy-admin-bundle20.php',
        Set::ELASTICSEARCH_DSL_50 => __DIR__ . '/../../config/set/elasticsearch-dsl50.php',
        Set::FRAMEWORK_EXTRA_BUNDLE_40 => __DIR__ . '/../../config/set/framework-extra-bundle-40.php',
        Set::FRAMEWORK_EXTRA_BUNDLE_50 => __DIR__ . '/../../config/set/framework-extra-bundle-50.php',
        Set::GMAGICK_TO_IMAGICK => __DIR__ . '/../../config/set/gmagick_to_imagick.php',
        Set::GUZZLE_50 => __DIR__ . '/../../config/set/guzzle50.php',
        Set::JMS_DECOUPLE => __DIR__ . '/../../config/set/jms-decouple.php',
        Set::KDYBY_EVENTS_TO_CONTRIBUTTE_EVENT_DISPATCHER => __DIR__ . '/../../config/set/kdyby-events-to-contributte-event-dispatcher.php',
        Set::KDYBY_TO_SYMFONY => __DIR__ . '/../../config/set/kdyby-to-symfony.php',
        Set::KDYBY_TRANSLATOR_TO_CONTRIBUTTE_TRANSLATION => __DIR__ . '/../../config/set/kdyby-translator-to-contributte-translation.php',
        Set::LARAVEL_50 => __DIR__ . '/../../config/set/laravel50.php',
        Set::LARAVEL_51 => __DIR__ . '/../../config/set/laravel51.php',
        Set::LARAVEL_52 => __DIR__ . '/../../config/set/laravel52.php',
        Set::LARAVEL_53 => __DIR__ . '/../../config/set/laravel53.php',
        Set::LARAVEL_54 => __DIR__ . '/../../config/set/laravel54.php',
        Set::LARAVEL_55 => __DIR__ . '/../../config/set/laravel55.php',
        Set::LARAVEL_56 => __DIR__ . '/../../config/set/laravel56.php',
        Set::LARAVEL_57 => __DIR__ . '/../../config/set/laravel57.php',
        Set::LARAVEL_58 => __DIR__ . '/../../config/set/laravel58.php',
        Set::LARAVEL_60 => __DIR__ . '/../../config/set/laravel60.php',
        Set::LARAVEL_STATIC_TO_INJECTION => __DIR__ . '/../../config/set/laravel-static-to-injection.php',
        Set::MOCKISTA_TO_MOCKERY => __DIR__ . '/../../config/set/mockista-to-mockery.php',
        Set::MONOLOG_20 => __DIR__ . '/../../config/set/monolog20.php',
        Set::MYSQL_TO_MYSQLI => __DIR__ . '/../../config/set/mysql-to-mysqli.php',
        Set::NAMING => __DIR__ . '/../../config/set/naming.php',
        Set::NETTE_30 => __DIR__ . '/../../config/set/nette-30.php',
        Set::NETTE_APPLICATION_CODE_QUALITY => __DIR__ . '/../../config/set/nette-application-code-quality.php',
        Set::NETTE_CONTROL_TO_SYMFONY_CONTROLLER => __DIR__ . '/../../config/set/nette-control-to-symfony-controller.php',
        Set::NETTE_FORMS_TO_SYMFONY => __DIR__ . '/../../config/set/nette-forms-to-symfony.php',
        Set::NETTE_PARAM_TYPES => __DIR__ . '/../../config/set/nette-30-param-types.php',
        Set::NETTE_RETURN_TYPES => __DIR__ . '/../../config/set/nette-30-return-types.php',
        Set::NETTE_TESTER_TO_PHPUNIT => __DIR__ . '/../../config/set/nette-tester-to-phpunit.php',
        Set::NETTE_TO_SYMFONY => __DIR__ . '/../../config/set/nette-to-symfony.php',
        Set::NETTE_UTILS_CODE_QUALITY => __DIR__ . '/../../config/set/nette-utils-code-quality.php',
        Set::ORDER => __DIR__ . '/../../config/set/order.php',
        Set::PERFORMANCE => __DIR__ . '/../../config/set/performance.php',
        Set::PHALCON_40 => __DIR__ . '/../../config/set/phalcon40.php',
        Set::PHPEXCEL_TO_PHPSPREADSHEET => __DIR__ . '/../../config/set/phpexcel-to-phpspreadsheet.php',
        Set::PHPSPEC_30 => __DIR__ . '/../../config/set/phpspec30.php',
        Set::PHPSPEC_40 => __DIR__ . '/../../config/set/phpspec40.php',
        Set::PHPSPEC_TO_PHPUNIT => __DIR__ . '/../../config/set/phpspec-to-phpunit.php',
        Set::PHPSTAN => __DIR__ . '/../../config/set/phpstan.php',
        Set::PHPUNIT80_DMS => __DIR__ . '/../../config/set/phpunit80-dms.php',
        Set::PHPUNIT_40 => __DIR__ . '/../../config/set/phpunit40.php',
        Set::PHPUNIT_50 => __DIR__ . '/../../config/set/phpunit50.php',
        Set::PHPUNIT_60 => __DIR__ . '/../../config/set/phpunit60.php',
        Set::PHPUNIT_70 => __DIR__ . '/../../config/set/phpunit70.php',
        Set::PHPUNIT_75 => __DIR__ . '/../../config/set/phpunit75.php',
        Set::PHPUNIT_80 => __DIR__ . '/../../config/set/phpunit80.php',
        Set::PHPUNIT_90 => __DIR__ . '/../../config/set/phpunit90.php',
        Set::PHPUNIT_91 => __DIR__ . '/../../config/set/phpunit91.php',
        Set::PHPUNIT_CODE_QUALITY => __DIR__ . '/../../config/set/phpunit-code-quality.php',
        Set::PHPUNIT_EXCEPTION => __DIR__ . '/../../config/set/phpunit-exception.php',
        Set::PHPUNIT_INJECTOR => __DIR__ . '/../../config/set/phpunit-injector.php',
        Set::PHPUNIT_MOCK => __DIR__ . '/../../config/set/phpunit-mock.php',
        Set::PHPUNIT_SPECIFIC_METHOD => __DIR__ . '/../../config/set/phpunit-specific-method.php',
        Set::PHPUNIT_YIELD_DATA_PROVIDER => __DIR__ . '/../../config/set/phpunit-yield-data-provider.php',
        Set::PHP_52 => __DIR__ . '/../../config/set/php52.php',
        Set::PHP_53 => __DIR__ . '/../../config/set/php53.php',
        Set::PHP_54 => __DIR__ . '/../../config/set/php54.php',
        Set::PHP_55 => __DIR__ . '/../../config/set/php55.php',
        Set::PHP_56 => __DIR__ . '/../../config/set/php56.php',
        Set::PHP_70 => __DIR__ . '/../../config/set/php70.php',
        Set::PHP_71 => __DIR__ . '/../../config/set/php71.php',
        Set::PHP_72 => __DIR__ . '/../../config/set/php72.php',
        Set::PHP_73 => __DIR__ . '/../../config/set/php73.php',
        Set::PHP_74 => __DIR__ . '/../../config/set/php74.php',
        Set::PHP_80 => __DIR__ . '/../../config/set/php80.php',
        Set::PHP_CODE_SNIFFER_30 => __DIR__ . '/../../config/set/php-code-sniffer30.php',
        Set::PHP_DI_DECOUPLE => __DIR__ . '/../../config/set/php-di-decouple.php',
        Set::PRIVATIZATION => __DIR__ . '/../../config/set/privatization.php',
        Set::PSR_4 => __DIR__ . '/../../config/set/psr-4.php',
        Set::SAFE_07 => __DIR__ . '/../../config/set/safe07.php',
        Set::SHOPWARE_55 => __DIR__ . '/../../config/set/shopware55.php',
        Set::SHOPWARE_56 => __DIR__ . '/../../config/set/shopware56.php',
        Set::SOLID => __DIR__ . '/../../config/set/solid.php',
        Set::SWIFTMAILER_60 => __DIR__ . '/../../config/set/swiftmailer60.php',
        Set::SYLIUS_10 => __DIR__ . '/../../config/set/sylius10.php',
        Set::SYLIUS_102 => __DIR__ . '/../../config/set/sylius102.php',
        Set::SYLIUS_109 => __DIR__ . '/../../config/set/sylius109.php',
        Set::SYMFONY50_TYPES => __DIR__ . '/../../config/set/symfony50-types.php',
        Set::SYMFONY_26 => __DIR__ . '/../../config/set/symfony26.php',
        Set::SYMFONY_28 => __DIR__ . '/../../config/set/symfony28.php',
        Set::SYMFONY_30 => __DIR__ . '/../../config/set/symfony30.php',
        Set::SYMFONY_31 => __DIR__ . '/../../config/set/symfony31.php',
        Set::SYMFONY_32 => __DIR__ . '/../../config/set/symfony32.php',
        Set::SYMFONY_33 => __DIR__ . '/../../config/set/symfony33.php',
        Set::SYMFONY_34 => __DIR__ . '/../../config/set/symfony34.php',
        Set::SYMFONY_40 => __DIR__ . '/../../config/set/symfony40.php',
        Set::SYMFONY_41 => __DIR__ . '/../../config/set/symfony41.php',
        Set::SYMFONY_42 => __DIR__ . '/../../config/set/symfony42.php',
        Set::SYMFONY_43 => __DIR__ . '/../../config/set/symfony43.php',
        Set::SYMFONY_44 => __DIR__ . '/../../config/set/symfony44.php',
        Set::SYMFONY_50 => __DIR__ . '/../../config/set/symfony50.php',
        Set::SYMFONY_CODE_QUALITY => __DIR__ . '/../../config/set/symfony-code-quality.php',
        Set::SYMFONY_CONSTRUCTOR_INJECTION => __DIR__ . '/../../config/set/symfony-constructor-injection.php',
        Set::SYMFONY_PHPUNIT => __DIR__ . '/../../config/set/symfony-phpunit.php',
        Set::TWIG_112 => __DIR__ . '/../../config/set/twig112.php',
        Set::TWIG_127 => __DIR__ . '/../../config/set/twig127.php',
        Set::TWIG_134 => __DIR__ . '/../../config/set/twig134.php',
        Set::TWIG_140 => __DIR__ . '/../../config/set/twig140.php',
        Set::TWIG_20 => __DIR__ . '/../../config/set/twig20.php',
        Set::TWIG_240 => __DIR__ . '/../../config/set/twig240.php',
        Set::TWIG_UNDERSCORE_TO_NAMESPACE => __DIR__ . '/../../config/set/twig-underscore-to-namespace.php',
        Set::TYPE_DECLARATION => __DIR__ . '/../../config/set/type-declaration.php',
        Set::UNWRAP_COMPAT => __DIR__ . '/../../config/set/unwrap-compat.php',
    ];

    public function __construct()
    {
        $this->validateSetFilePaths();
    }

    /**
     * @return array<string, string>
     */
    public function provide(): array
    {
        return self::SET_PATH_TO_FILEPATH;
    }

    /**
     * @return string[]
     */
    public function provideSetNames(): array
    {
        return array_keys(self::SET_PATH_TO_FILEPATH);
    }

    public function provideFilePathByName(string $setName): ?string
    {
        return self::SET_PATH_TO_FILEPATH[$setName] ?? null;
    }

    private function validateSetFilePaths(): void
    {
        foreach (self::SET_PATH_TO_FILEPATH as $setName => $setFilePath) {
            new SmartFileInfo($setFilePath);
        }
    }
}
