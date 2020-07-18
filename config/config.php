<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../packages/**/config/config.*');

    $containerConfigurator->import(__DIR__ . '/../rules/**/config/config.*');

    $containerConfigurator->import(__DIR__ . '/services.php');

    $containerConfigurator->import(__DIR__ . '/parameters/parameter_name_guard.php');

    $containerConfigurator->import(__DIR__ . '/../utils/**/config/config.php', null, true);

    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::PATHS, []);

    $parameters->set(Option::FILE_EXTENSIONS, ['php']);

    $parameters->set(Option::EXCLUDE_PATHS, []);

    $parameters->set(Option::EXCLUDE_RECTORS, []);

    $parameters->set(Option::AUTOLOAD_PATHS, []);

    $parameters->set('rector_recipe', []);

    $parameters->set('project_type', 'proprietary');

    $parameters->set('nested_chain_method_call_limit', 30);

    $parameters->set(Option::AUTO_IMPORT_NAMES, false);

    $parameters->set('import_short_classes', true);

    $parameters->set('import_doc_blocks', true);

    $parameters->set('php_version_features', null);

    $parameters->set('safe_types', false);
};
