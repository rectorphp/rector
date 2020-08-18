<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'PHP_CodeSniffer_Sniffs_Sniff' => 'PHP_CodeSniffer\Sniffs\Sniff',
                'PHP_CodeSniffer_File' => 'PHP_CodeSniffer\Files\File',
                'PHP_CodeSniffer_Tokens' => 'PHP_CodeSniffer\Util\Tokens',
            ],
        ]]);
};
