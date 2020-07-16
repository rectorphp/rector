<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/../packages/**/config/config.*');

    $containerConfigurator->import(__DIR__ . '/../rules/**/config/config.*');

    $containerConfigurator->import(__DIR__ . '/services.php');

    $containerConfigurator->import(__DIR__ . '/parameters/parameter_name_guard.php');

    $containerConfigurator->import(__DIR__ . '/../utils/**/config/config.yaml', null, true);

    $parameters = $containerConfigurator->parameters();

    $parameters->set('paths', []);

    $parameters->set('file_extensions', ['php']);

    $parameters->set('exclude_paths', []);

    $parameters->set('exclude_rectors', []);

    $parameters->set('autoload_paths', []);

    $parameters->set('rector_recipe', []);

    $parameters->set('project_type', 'proprietary');

    $parameters->set('nested_chain_method_call_limit', 30);

    $parameters->set('auto_import_names', false);

    $parameters->set('import_short_classes', true);

    $parameters->set('import_doc_blocks', true);

    $parameters->set('php_version_features', null);

    $parameters->set('safe_types', false);
};
