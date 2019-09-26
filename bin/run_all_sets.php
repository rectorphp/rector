<?php declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Set\SetProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Symplify\PackageBuilder\Console\ShellCode;

require __DIR__ . '/../vendor/autoload.php';

$setProvider = new SetProvider();

// any file to be tested
$file = 'src/Rector/AbstractRector.php';
$excludedSets = [
    // required Kernel class to be set in parameters
    'symfony-code-quality'
];

$errors = [];
foreach ($setProvider->provide() as $setName) {
    if (in_array($setName, $excludedSets, true)) {
        continue;
    }

    $command = ['php', 'bin/rector', 'process', $file, '--set', $setName, '--dry-run'];


    $process = new Process($command, __DIR__ . '/..');
    echo sprintf('Set "%s" is OK' . PHP_EOL, $setName);

    try {
        $process->mustRun();
    } catch (ProcessFailedException $processFailedException) {
        if (! Strings::match($processFailedException->getMessage(), '#(Fatal error)|(\[ERROR\])#')) {
            continue;
        }

        $errors[] = $processFailedException->getMessage();
    }
}

if ($errors === []) {
    exit(ShellCode::SUCCESS);
}

foreach ($errors as $error) {
    echo $error;
    echo PHP_EOL;
}

exit(ShellCode::ERROR);
