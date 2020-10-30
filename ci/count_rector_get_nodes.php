<?php

// report most often types in `RectorInterface::getNodeTypes()`

declare(strict_types=1);

use Rector\Core\Console\Style\SymfonyStyleFactory;
use Rector\Core\Testing\Finder\RectorsFinder;
use Symplify\PackageBuilder\Reflection\PrivatesCaller;

require __DIR__ . '/../vendor/autoload.php';

$rectorsFinder = new RectorsFinder();
$phpRectors = $rectorsFinder->findAndCreatePhpRectors();

$nodeTypes = [];
foreach ($phpRectors as $phpRector) {
    foreach ($phpRector->getNodeTypes() as $nodeType) {
        $nodeTypes[] = $nodeType;
    }
}

$nodeTypesCount = array_count_values($nodeTypes);
arsort($nodeTypesCount);

$symfonyStyleFactory = new SymfonyStyleFactory(new PrivatesCaller());
$symfonyStyle = $symfonyStyleFactory->create();


$rows = [];
foreach ($nodeTypesCount as $nodeType => $count) {
    $rows[] = [$nodeType, $count];
}

$symfonyStyle->table(['Node Type', 'Rector Count'], $rows);
$symfonyStyle->newLine();

$uniqueNodeTypes = count(array_unique($nodeTypes));
$message = sprintf('In total, %d Rectors listens to %d node types - with only %d unique types', count($phpRectors), count($nodeTypes), $uniqueNodeTypes);
$symfonyStyle->success($message);

