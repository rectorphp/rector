<?php

declare(strict_types=1);

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set('correct_to_typos', [
        Option::EXCLUDE_PATHS => [
            'exclude',
            'excluded',
            'exclude_path',
            'excluded_path',
            'exclude_dir',
            'excluded_dir',
            'exclude_dirs',
            'excluded_dirs',
            'exclude_file',
            'excluded_file',
            'exclude_files',
            'excluded_files',
            'ignore_path',
            'ignored_path',
            'ignore_paths',
            'ignored_paths',
            'ignore_dir',
            'ignored_dir',
            'ignore_dirs',
            'ignored_dirs',
            'ignore_file',
            'ignored_file',
            'ignore_files',
            'ignored_files',
            'skip_path',
            'skip_paths',
            'skip_dir',
            'skip_dirs',
            'skip_file',
            'skip_files',
        ],
        Option::EXCLUDE_RECTORS => [
            'exclude_rector',
            'excluded_rector',
            'excluded_rectors',
            'skip_rector',
            'skip_rectors',
        ],
        Option::AUTOLOAD_PATHS => ['#(autolaod|autoload|include|bootstrap)((ed)?_(path(s)?|dir(s)?|file(s)?))?#'],
        Option::AUTO_IMPORT_NAMES => [
            'auto_imort_names',
            'auto_import_name',
            'auto_imports_names',
            'auto_imports_name',
            'auto_names',
            'import_name(space)?(s)?',
        ],
        Option::PATHS => ['path', 'source'],
    ]);
};
