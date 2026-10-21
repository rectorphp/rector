<?php

declare (strict_types=1);
namespace RectorPrefix202610;

/**
 * Benchmark for CastDoctrineExprToStringRector.
 *
 * The rule targets a small set of Doctrine Expr method names. refactor() used to run isObjectType()
 * (type inference) on the caller before comparing the method name, so the expensive type resolution
 * ran on every method call in a file - including the overwhelming majority whose name is none of the
 * target methods and bail anyway.
 *
 * Checking the cheap method name first lets a non-matching call bail before any isObjectType().
 *
 * This bench drives refactor() over many method calls whose names are NOT targets - the common case.
 * CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Expr\MethodCall;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\Doctrine\Orm30\Rector\MethodCall\CastDoctrineExprToStringRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('CALLS', 300);
\define('REPEAT', 200);
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(CastDoctrineExprToStringRector::class);
$source = "<?php\n\nnamespace Bench;\n\n\$service = new SomeService();\n";
for ($i = 0; $i < \CALLS; ++$i) {
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
