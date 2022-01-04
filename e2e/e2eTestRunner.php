#!/usr/bin/env php
<?php

// runs a rector e2e test.
// checks whether we expect a certain output, or alternatively that rector just processed everything without errors

use SebastianBergmann\Diff\Differ;
use Symfony\Component\Console\Command\Command;
use Symplify\ConsoleColorDiff\Console\Formatter\ColorConsoleDiffFormatter;
use Symplify\ConsoleColorDiff\Console\Output\ConsoleDiffer;
use Symplify\PackageBuilder\Console\Style\SymfonyStyleFactory;

$projectRoot = __DIR__ .'/..';
$rectorBin = $projectRoot . '/bin/rector';
$autoloadFile = $projectRoot . '/vendor/autoload.php';

require_once $autoloadFile;

$e2eCommand = 'php '. $rectorBin .' process --dry-run --no-ansi --no-progress-bar -a '. $autoloadFile . ' --clear-cache';

if (isset($argv[1]) && $argv[1] === '-c') {
    $e2eCommand .= ' -c ' . $argv[2];
}

if (isset($argv[1]) && $argv[1] === '--config') {
    $e2eCommand .= ' --config ' . $argv[2];
}

exec($e2eCommand, $output, $exitCode);
$output = trim(implode("\n", $output));

$expectedDiff = 'expected-output.diff';
if (!file_exists($expectedDiff)) {
    echo $output;
    exit($exitCode);
}

$symfonyStyleFactory = new SymfonyStyleFactory();
$symfonyStyle =  $symfonyStyleFactory->create();

$matchedExpectedOutput = false;
$expectedOutput = trim(file_get_contents($expectedDiff));
if ($output === $expectedOutput) {
    $symfonyStyle->success('End-to-end test successfully completed');
    exit(Command::SUCCESS);
}

// print color diff, to make easy find the differences
$consoleDiffer = new ConsoleDiffer(new Differ(), new ColorConsoleDiffFormatter());
$diff = $consoleDiffer->diff($output, $expectedOutput);
$symfonyStyle->writeln($diff);

exit(Command::FAILURE);
