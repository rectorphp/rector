<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Rector\ConsoleDiffer\Diff\Output\CompleteUnifiedDiffOutputBuilderFactory;
use Rector\ConsoleDiffer\DifferAndFormatter;
use Rector\ConsoleDiffer\MarkdownDifferAndFormatter;
use SebastianBergmann\Diff\Differ;
use SebastianBergmann\Diff\Output\StrictUnifiedDiffOutputBuilder;
use SebastianBergmann\Diff\Output\UnifiedDiffOutputBuilder;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->defaults()
        ->public()
        ->autowire();

    $services->load('Rector\ConsoleDiffer\\', __DIR__ . '/../src');

    $services->set(DifferAndFormatter::class)
        ->args(['$differ' => ref('differ')]);

    $services->set(MarkdownDifferAndFormatter::class)
        ->args(['$markdownDiffer' => ref('markdownDiffer')]);

    $services->set('diffOutputBuilder', StrictUnifiedDiffOutputBuilder::class)
        ->args([
            '$options' => [
                'fromFile' => 'Original',
                'toFile' => 'New',
            ],
        ]);

    $services->set('differ', Differ::class)
        ->args([ref('diffOutputBuilder')]);

    $services->set('markdownDiffOutputBuilder', UnifiedDiffOutputBuilder::class)
        ->factory([ref(CompleteUnifiedDiffOutputBuilderFactory::class), 'create']);

    $services->set('markdownDiffer', Differ::class)
        ->args([ref('markdownDiffOutputBuilder')]);

    $services->set(ColorConsoleDiffFormatter::class);

    $services->set(ConsoleDiffer::class);

    $services->set(SymfonyStyleFactory::class);

    $services->set(SymfonyStyle::class)
        ->factory([ref(SymfonyStyleFactory::class), 'create']);
};
