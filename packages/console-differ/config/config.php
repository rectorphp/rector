<?php

declare(strict_types=1);

use Rector\ConsoleDiffer\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire()
        ->autoconfigure();

    $services->load('Rector\ConsoleDiffer\\', __DIR__ . '/../src');

    $services->set(DifferAndFormatter::class)
        ->arg('$differ', service('differ'));

    $services->set(MarkdownDifferAndFormatter::class)
        ->arg('$markdownDiffer', service('markdownDiffer'));

    $services->set('diffOutputBuilder', StrictUnifiedDiffOutputBuilder::class)
        ->arg('$options', [
            'fromFile' => 'Original',
            'toFile' => 'New',
        ]);

    $services->set('differ', Differ::class)
        ->arg('$outputBuilder', service('diffOutputBuilder'));

    $services->set('markdownDiffOutputBuilder', UnifiedDiffOutputBuilder::class)
        ->factory([service(CompleteUnifiedDiffOutputBuilderFactory::class), 'create']);

    $services->set('markdownDiffer', Differ::class)
        ->arg('$outputBuilder', service('markdownDiffOutputBuilder'));

    $services->set(ColorConsoleDiffFormatter::class);

    $services->set(ConsoleDiffer::class);
};
