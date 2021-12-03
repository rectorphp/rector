#!/usr/bin/env php
<?php

// runs a rector e2e test.
// checks whether we expect a certain output, or alternatively that rector just processed everything without errors

$projectRoot = __DIR__ .'/../';
$rectorBin = $projectRoot .'bin/rector';
$autoloadFile = $projectRoot .'vendor/autoload.php';

$e2eCommand = 'php '. $rectorBin .' process --dry-run --no-ansi --no-progress-bar -a '. $autoloadFile;
exec($e2eCommand, $output, $exitCode);
$output = trim(implode("\n", $output));

$expectedDiff = 'expected-output.diff';
if (!file_exists($expectedDiff)) {
    echo $output;
    exit($exitCode);
}

$matchedExpectedOutput = false;
$expectedOutput = trim(file_get_contents($expectedDiff));
if ($output === $expectedOutput) {
    $matchedExpectedOutput = true;
}

if ($matchedExpectedOutput === false) {
    echo "\nEXPECTED:\n";
    var_dump($expectedOutput);
    echo "\nACTUAL:\n";
    var_dump($output);
    exit(1);
}

echo "[OK] end-to-end test successfully completed.";
exit(0);
