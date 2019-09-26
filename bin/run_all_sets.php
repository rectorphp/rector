<?php declare(strict_types=1);

use Nette\Utils\Strings;
use Rector\Set\SetProvider;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

require __DIR__ . '/../vendor/autoload.php';

$setProvider = new SetProvider();

// any file to be tested
$file = 'src/Rector/AbstractRector.php';

foreach ($setProvider->provide() as $setName) {
    $command = ['php', 'bin/rector', 'process', $file, '--set', $setName, '--dry-run'];

    $process = new Process($command, __DIR__ . '/..');
    echo sprintf('Set "%s" is OK' . PHP_EOL, $setName);

    try {
        $process->mustRun();
    } catch (ProcessFailedException $processFailedException) {
        if (! Strings::match($processFailedException->getMessage(), '#(Fatal error)|(\[ERROR\])#')) {
            continue;
        }

        echo $processFailedException->getMessage();
        exit(1);
    }
}
