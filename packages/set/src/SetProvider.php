<?php

declare(strict_types=1);

namespace Rector\Set;

use Rector\Set\ValueObject\SetList;
use Symplify\SetConfigResolver\Provider\AbstractSetProvider;
use Symplify\SetConfigResolver\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetProvider extends AbstractSetProvider
{
    /**
     * @var Set[]
     */
    private $sets = [];

    public function __construct()
    {
        $setNamesToSetPaths = [
            SetList::DEFLUENT => __DIR__ . '/../../../config/set/defluent.php',
            SetList::ACTION_INJECTION_TO_CONSTRUCTOR_INJECTION => __DIR__ . '/../../../config/set/action-injection-to-constructor-injection.php',
            SetList::ARRAY_STR_FUNCTIONS_TO_STATIC_CALL => __DIR__ . '/../../../config/set/laravel-array-str-functions-to-static-call.php',
            SetList::CAKEPHP_30 => __DIR__ . '/../../../config/set/cakephp30.php',
            SetList::CAKEPHP_34 => __DIR__ . '/../../../config/set/cakephp34.php',
            SetList::CAKEPHP_35 => __DIR__ . '/../../../config/set/cakephp35.php',
            SetList::CAKEPHP_36 => __DIR__ . '/../../../config/set/cakephp36.php',
            SetList::CAKEPHP_37 => __DIR__ . '/../../../config/set/cakephp37.php',
            SetList::CAKEPHP_38 => __DIR__ . '/../../../config/set/cakephp38.php',
            SetList::CAKEPHP_40 => __DIR__ . '/../../../config/set/cakephp40.php',
            SetList::CAKEPHP_FLUENT_OPTIONS => __DIR__ . '/../../../config/set/cakephp-fluent-options.php',
            SetList::CODEIGNITER_40 => __DIR__ . '/../../../config/set/codeigniter-40.php',
            SetList::CODE_QUALITY => __DIR__ . '/../../../config/set/code-quality.php',
            SetList::CODING_STYLE => __DIR__ . '/../../../config/set/coding-style.php',
            SetList::CONSTRUCTOR_INJECTIN_TO_ACTION_INJECTION => __DIR__ . '/../../../config/set/constructor-injectin-to-action-injection.php',
            SetList::CONTRIBUTTE_TO_SYMFONY => __DIR__ . '/../../../config/set/contributte-to-symfony.php',
            SetList::DEAD_CLASSES => __DIR__ . '/../../../config/set/dead-classes.php',
            SetList::DEAD_CODE => __DIR__ . '/../../../config/set/dead-code.php',
            SetList::DOCTRINE_25 => __DIR__ . '/../../../config/set/doctrine25.php',
            SetList::DOCTRINE_BEHAVIORS_20 => __DIR__ . '/../../../config/set/doctrine-behaviors-20.php',
            SetList::DOCTRINE_CODE_QUALITY => __DIR__ . '/../../../config/set/doctrine-code-quality.php',
            SetList::DOCTRINE_COMMON_20 => __DIR__ . '/../../../config/set/doctrine-common-20.php',
            SetList::DOCTRINE_DBAL_210 => __DIR__ . '/../../../config/set/doctrine-dbal-210.php',
            SetList::DOCTRINE_DBAL_30 => __DIR__ . '/../../../config/set/doctrine-dbal-30.php',
            SetList::DOCTRINE_GEDMO_TO_KNPLABS => __DIR__ . '/../../../config/set/doctrine-gedmo-to-knplabs.php',
            SetList::DOCTRINE_ID_TO_UUID_STEP_1 => __DIR__ . '/../../../config/set/doctrine-id-to-uuid-step-1.php',
            SetList::DOCTRINE_ID_TO_UUID_STEP_2 => __DIR__ . '/../../../config/set/doctrine-id-to-uuid-step-2.php',
            SetList::DOCTRINE_ID_TO_UUID_STEP_3 => __DIR__ . '/../../../config/set/doctrine-id-to-uuid-step-3.php',
            SetList::DOCTRINE_ID_TO_UUID_STEP_4 => __DIR__ . '/../../../config/set/doctrine-id-to-uuid-step-4.php',
            SetList::DOCTRINE_ID_TO_UUID_STEP_5 => __DIR__ . '/../../../config/set/doctrine-id-to-uuid-step-5.php',
            SetList::DOCTRINE_REPOSITORY_AS_SERVICE => __DIR__ . '/../../../config/set/doctrine-repository-as-service.php',
            SetList::DOCTRINE_SERVICES => __DIR__ . '/../../../config/set/doctrine-services.php',
            SetList::DOWNGRADE => __DIR__ . '/../../../config/set/downgrade.php',
            SetList::EASY_ADMIN_BUNDLE_20 => __DIR__ . '/../../../config/set/easy-admin-bundle20.php',
            SetList::ELASTICSEARCH_DSL_50 => __DIR__ . '/../../../config/set/elasticsearch-dsl50.php',
            SetList::FRAMEWORK_EXTRA_BUNDLE_40 => __DIR__ . '/../../../config/set/framework-extra-bundle-40.php',
            SetList::FRAMEWORK_EXTRA_BUNDLE_50 => __DIR__ . '/../../../config/set/framework-extra-bundle-50.php',
            SetList::GMAGICK_TO_IMAGICK => __DIR__ . '/../../../config/set/gmagick_to_imagick.php',
            SetList::GUZZLE_50 => __DIR__ . '/../../../config/set/guzzle50.php',
            SetList::JMS_DECOUPLE => __DIR__ . '/../../../config/set/jms-decouple.php',
            SetList::KDYBY_EVENTS_TO_CONTRIBUTTE_EVENT_DISPATCHER => __DIR__ . '/../../../config/set/kdyby-events-to-contributte-event-dispatcher.php',
            SetList::KDYBY_TO_SYMFONY => __DIR__ . '/../../../config/set/kdyby-to-symfony.php',
            SetList::KDYBY_TRANSLATOR_TO_CONTRIBUTTE_TRANSLATION => __DIR__ . '/../../../config/set/kdyby-translator-to-contributte-translation.php',
            SetList::LARAVEL_50 => __DIR__ . '/../../../config/set/laravel50.php',
            SetList::LARAVEL_51 => __DIR__ . '/../../../config/set/laravel51.php',
            SetList::LARAVEL_52 => __DIR__ . '/../../../config/set/laravel52.php',
            SetList::LARAVEL_53 => __DIR__ . '/../../../config/set/laravel53.php',
            SetList::LARAVEL_54 => __DIR__ . '/../../../config/set/laravel54.php',
            SetList::LARAVEL_55 => __DIR__ . '/../../../config/set/laravel55.php',
            SetList::LARAVEL_56 => __DIR__ . '/../../../config/set/laravel56.php',
            SetList::LARAVEL_57 => __DIR__ . '/../../../config/set/laravel57.php',
            SetList::LARAVEL_58 => __DIR__ . '/../../../config/set/laravel58.php',
            SetList::LARAVEL_60 => __DIR__ . '/../../../config/set/laravel60.php',
            SetList::LARAVEL_STATIC_TO_INJECTION => __DIR__ . '/../../../config/set/laravel-static-to-injection.php',
            SetList::MOCKISTA_TO_MOCKERY => __DIR__ . '/../../../config/set/mockista-to-mockery.php',
            SetList::MONOLOG_20 => __DIR__ . '/../../../config/set/monolog20.php',
            SetList::MYSQL_TO_MYSQLI => __DIR__ . '/../../../config/set/mysql-to-mysqli.php',
            SetList::NAMING => __DIR__ . '/../../../config/set/naming.php',
            SetList::NETTE_30 => __DIR__ . '/../../../config/set/nette-30.php',
            SetList::NETTE_CODE_QUALITY => __DIR__ . '/../../../config/set/nette-code-quality.php',
            SetList::NETTE_CONTROL_TO_SYMFONY_CONTROLLER => __DIR__ . '/../../../config/set/nette-control-to-symfony-controller.php',
            SetList::NETTE_FORMS_TO_SYMFONY => __DIR__ . '/../../../config/set/nette-forms-to-symfony.php',
            SetList::NETTE_PARAM_TYPES => __DIR__ . '/../../../config/set/nette-30-param-types.php',
            SetList::NETTE_RETURN_TYPES => __DIR__ . '/../../../config/set/nette-30-return-types.php',
            SetList::NETTE_TESTER_TO_PHPUNIT => __DIR__ . '/../../../config/set/nette-tester-to-phpunit.php',
            SetList::NETTE_TO_SYMFONY => __DIR__ . '/../../../config/set/nette-to-symfony.php',
            SetList::NETTE_UTILS_CODE_QUALITY => __DIR__ . '/../../../config/set/nette-utils-code-quality.php',
            SetList::ORDER => __DIR__ . '/../../../config/set/order.php',
            SetList::PERFORMANCE => __DIR__ . '/../../../config/set/performance.php',
            SetList::PHALCON_40 => __DIR__ . '/../../../config/set/phalcon40.php',
            SetList::PHPEXCEL_TO_PHPSPREADSHEET => __DIR__ . '/../../../config/set/phpexcel-to-phpspreadsheet.php',
            SetList::PHPSPEC_30 => __DIR__ . '/../../../config/set/phpspec30.php',
            SetList::PHPSPEC_40 => __DIR__ . '/../../../config/set/phpspec40.php',
            SetList::PHPSPEC_TO_PHPUNIT => __DIR__ . '/../../../config/set/phpspec-to-phpunit.php',
            SetList::PHPSTAN => __DIR__ . '/../../../config/set/phpstan.php',
            SetList::PHPUNIT80_DMS => __DIR__ . '/../../../config/set/phpunit80-dms.php',
            SetList::PHPUNIT_40 => __DIR__ . '/../../../config/set/phpunit40.php',
            SetList::PHPUNIT_50 => __DIR__ . '/../../../config/set/phpunit50.php',
            SetList::PHPUNIT_60 => __DIR__ . '/../../../config/set/phpunit60.php',
            SetList::PHPUNIT_70 => __DIR__ . '/../../../config/set/phpunit70.php',
            SetList::PHPUNIT_75 => __DIR__ . '/../../../config/set/phpunit75.php',
            SetList::PHPUNIT_80 => __DIR__ . '/../../../config/set/phpunit80.php',
            SetList::PHPUNIT_90 => __DIR__ . '/../../../config/set/phpunit90.php',
            SetList::PHPUNIT_91 => __DIR__ . '/../../../config/set/phpunit91.php',
            SetList::PHPUNIT_CODE_QUALITY => __DIR__ . '/../../../config/set/phpunit-code-quality.php',
            SetList::PHPUNIT_EXCEPTION => __DIR__ . '/../../../config/set/phpunit-exception.php',
            SetList::PHPUNIT_INJECTOR => __DIR__ . '/../../../config/set/phpunit-injector.php',
            SetList::PHPUNIT_MOCK => __DIR__ . '/../../../config/set/phpunit-mock.php',
            SetList::PHPUNIT_SPECIFIC_METHOD => __DIR__ . '/../../../config/set/phpunit-specific-method.php',
            SetList::PHPUNIT_YIELD_DATA_PROVIDER => __DIR__ . '/../../../config/set/phpunit-yield-data-provider.php',
            SetList::PHP_52 => __DIR__ . '/../../../config/set/php52.php',
            SetList::PHP_53 => __DIR__ . '/../../../config/set/php53.php',
            SetList::PHP_54 => __DIR__ . '/../../../config/set/php54.php',
            SetList::PHP_55 => __DIR__ . '/../../../config/set/php55.php',
            SetList::PHP_56 => __DIR__ . '/../../../config/set/php56.php',
            SetList::PHP_70 => __DIR__ . '/../../../config/set/php70.php',
            SetList::PHP_71 => __DIR__ . '/../../../config/set/php71.php',
            SetList::PHP_72 => __DIR__ . '/../../../config/set/php72.php',
            SetList::PHP_73 => __DIR__ . '/../../../config/set/php73.php',
            SetList::PHP_74 => __DIR__ . '/../../../config/set/php74.php',
            SetList::PHP_80 => __DIR__ . '/../../../config/set/php80.php',
            SetList::PHP_CODE_SNIFFER_30 => __DIR__ . '/../../../config/set/php-code-sniffer30.php',
            SetList::PHP_DI_DECOUPLE => __DIR__ . '/../../../config/set/php-di-decouple.php',
            SetList::PRIVATIZATION => __DIR__ . '/../../../config/set/privatization.php',
            SetList::PSR_4 => __DIR__ . '/../../../config/set/psr-4.php',
            SetList::SAFE_07 => __DIR__ . '/../../../config/set/safe07.php',
            SetList::SHOPWARE_55 => __DIR__ . '/../../../config/set/shopware55.php',
            SetList::SHOPWARE_56 => __DIR__ . '/../../../config/set/shopware56.php',
            SetList::SOLID => __DIR__ . '/../../../config/set/solid.php',
            SetList::SWIFTMAILER_60 => __DIR__ . '/../../../config/set/swiftmailer60.php',
            SetList::SYLIUS_10 => __DIR__ . '/../../../config/set/sylius10.php',
            SetList::SYLIUS_102 => __DIR__ . '/../../../config/set/sylius102.php',
            SetList::SYLIUS_109 => __DIR__ . '/../../../config/set/sylius109.php',
            SetList::SYMFONY_26 => __DIR__ . '/../../../config/set/symfony26.php',
            SetList::SYMFONY_28 => __DIR__ . '/../../../config/set/symfony28.php',
            SetList::SYMFONY_30 => __DIR__ . '/../../../config/set/symfony30.php',
            SetList::SYMFONY_31 => __DIR__ . '/../../../config/set/symfony31.php',
            SetList::SYMFONY_32 => __DIR__ . '/../../../config/set/symfony32.php',
            SetList::SYMFONY_33 => __DIR__ . '/../../../config/set/symfony33.php',
            SetList::SYMFONY_34 => __DIR__ . '/../../../config/set/symfony34.php',
            SetList::SYMFONY_40 => __DIR__ . '/../../../config/set/symfony40.php',
            SetList::SYMFONY_41 => __DIR__ . '/../../../config/set/symfony41.php',
            SetList::SYMFONY_42 => __DIR__ . '/../../../config/set/symfony42.php',
            SetList::SYMFONY_43 => __DIR__ . '/../../../config/set/symfony43.php',
            SetList::SYMFONY_44 => __DIR__ . '/../../../config/set/symfony44.php',
            SetList::SYMFONY_50 => __DIR__ . '/../../../config/set/symfony50.php',
            SetList::SYMFONY_50_TYPES => __DIR__ . '/../../../config/set/symfony50-types.php',
            SetList::SYMFONY_PHP_CONFIG => __DIR__ . '/../../../config/set/symfony-php-config.php',
            SetList::SYMFONY_CODE_QUALITY => __DIR__ . '/../../../config/set/symfony-code-quality.php',
            SetList::SYMFONY_CONSTRUCTOR_INJECTION => __DIR__ . '/../../../config/set/symfony-constructor-injection.php',
            SetList::SYMFONY_PHPUNIT => __DIR__ . '/../../../config/set/symfony-phpunit.php',
            SetList::TWIG_112 => __DIR__ . '/../../../config/set/twig112.php',
            SetList::TWIG_127 => __DIR__ . '/../../../config/set/twig127.php',
            SetList::TWIG_134 => __DIR__ . '/../../../config/set/twig134.php',
            SetList::TWIG_140 => __DIR__ . '/../../../config/set/twig140.php',
            SetList::TWIG_20 => __DIR__ . '/../../../config/set/twig20.php',
            SetList::TWIG_240 => __DIR__ . '/../../../config/set/twig240.php',
            SetList::TWIG_UNDERSCORE_TO_NAMESPACE => __DIR__ . '/../../../config/set/twig-underscore-to-namespace.php',
            SetList::TYPE_DECLARATION => __DIR__ . '/../../../config/set/type-declaration.php',
            SetList::UNWRAP_COMPAT => __DIR__ . '/../../../config/set/unwrap-compat.php',
        ];

        foreach ($setNamesToSetPaths as $setName => $setPath) {
            $this->sets[] = new Set($setName, new SmartFileInfo($setPath));
        }
    }

    /**
     * @return Set[]
     */
    public function provide(): array
    {
        return $this->sets;
    }
}
