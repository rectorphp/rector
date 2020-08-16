<?php

declare(strict_types=1);

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameClassRector::class)
        ->call('configure', [[
            RenameClassRector::OLD_TO_NEW_CLASSES => [
                'Kdyby\Translation\Translator' => 'Nette\Localization\ITranslator',
                'Kdyby\Translation\DI\ITranslationProvider' => 'Contributte\Translation\DI\TranslationProviderInterface',
                'Kdyby\Translation\Phrase' => 'Contributte\Translation\Wrappers\Message',
            ],
        ]]);
};
