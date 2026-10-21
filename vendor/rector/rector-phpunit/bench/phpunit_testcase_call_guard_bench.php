<?php

declare (strict_types=1);
namespace RectorPrefix202610;

/**
 * Benchmark for the PHPUnit test-case call guard shared by ~25 assert rules.
 *
 * Every assert rule subscribes to MethodCall/StaticCall and gates on
 * TestsNodeAnalyzer::isPHPUnitMethodCallNames(). That method used to run
 * NodeTypeResolver::getType() on the caller first and only then compare the method name, so the
 * expensive type inference ran on every method call in the file - including the overwhelming
 * majority that are not assert* calls and bail on the name anyway.
 *
 * Checking the cheap method name first lets a non-matching call bail before any getType().
 *
 * This bench drives AssertInstanceOfComparisonRector::refactor() (a typical consumer) over many
 * method calls whose names are NOT assert methods - the common case in any real file.
 *
 * Run on both branches to compare. CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Expr\MethodCall;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\CodeQuality\Rector\MethodCall\AssertInstanceOfComparisonRector;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('CALLS', 300);
\define('REPEAT', 200);
// outer repeats
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(AssertInstanceOfComparisonRector::class);
$source = "<?php\n\nnamespace Bench;\n\n\$service = new SomeService();\n";
for ($i = 0; $i < \CALLS; ++$i) {
    // ordinary, non-assert method calls - the common case that must bail cheaply
    $source .= "\$service->handle{$i}(\$value, {$i});\n";
}
$sourceFilePath = \tempnam(\sys_get_temp_dir(), 'bench_') . '.php';
\file_put_contents($sourceFilePath, $source);
$stmts = $testingParser->parseFileToDecoratedNodes($sourceFilePath);
/** @var MethodCall[] $methodCalls */
$methodCalls = $betterNodeFinder->findInstanceOf($stmts, MethodCall::class);
$cpuSeconds = static function (): float {
    $usage = \getrusage();
    return $usage['ru_utime.tv_sec'] + $usage['ru_utime.tv_usec'] / 1000000 + $usage['ru_stime.tv_sec'] + $usage['ru_stime.tv_usec'] / 1000000;
};
$run = static function () use ($rector, $methodCalls): void {
    foreach ($methodCalls as $methodCall) {
        $rector->refactor($methodCall);
    }
};
// warm up caches once, excluded from timing
$run();
$wallStart = \microtime(\true);
$cpuStart = $cpuSeconds();
for ($repeat = 0; $repeat < \REPEAT; ++$repeat) {
    $run();
}
$cpu = $cpuSeconds() - $cpuStart;
$wall = \microtime(\true) - $wallStart;
@\unlink($sourceFilePath);
$totalCalls = \REPEAT * \count($methodCalls);
\printf('calls: %d, repeats: %d, refactor() calls: %d%s', \count($methodCalls), \REPEAT, $totalCalls, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f us/call)%s', $cpu, $cpu / $totalCalls * 1000000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
