<?php

declare (strict_types=1);
namespace RectorPrefix202610;

/**
 * Benchmark for ExecuteQueryParamsToBindValueRector.
 *
 * The rule walks the statements of a stmts-aware node and, for every MethodCall it finds, used to
 * run isObjectType() (type inference) on the caller before checking the method name - so the
 * expensive type resolution ran on every method call in the body, including the overwhelming
 * majority that are not executeQuery() and bail on the name anyway.
 *
 * Checking the cheap method name first lets a non-matching call bail before any isObjectType().
 *
 * This bench drives refactor() over a method body full of non-executeQuery calls - the common case.
 * CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\Doctrine\Dbal40\Rector\StmtsAwareInterface\ExecuteQueryParamsToBindValueRector;
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
$rector = $rectorConfig->make(ExecuteQueryParamsToBindValueRector::class);
$source = "<?php\n\nnamespace Bench;\n\nclass SomeService\n{\n    public function run(\$connection)\n    {\n";
for ($i = 0; $i < \CALLS; ++$i) {
    // ordinary, non-executeQuery calls in the body - the common case that must bail cheaply
    $source .= "        \$connection->handle{$i}(\$value, {$i});\n";
}
$source .= "    }\n}\n";
$sourceFilePath = \tempnam(\sys_get_temp_dir(), 'bench_') . '.php';
\file_put_contents($sourceFilePath, $source);
$stmts = $testingParser->parseFileToDecoratedNodes($sourceFilePath);
/** @var ClassMethod[] $classMethods */
$classMethods = $betterNodeFinder->findInstanceOf($stmts, ClassMethod::class);
$cpuSeconds = static function (): float {
    $usage = \getrusage();
    return $usage['ru_utime.tv_sec'] + $usage['ru_utime.tv_usec'] / 1000000 + $usage['ru_stime.tv_sec'] + $usage['ru_stime.tv_usec'] / 1000000;
};
$run = static function () use ($rector, $classMethods): void {
    foreach ($classMethods as $classMethod) {
        $rector->refactor($classMethod);
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
$totalCalls = \REPEAT * \CALLS;
\printf('body calls: %d, repeats: %d, inner method calls visited: %d%s', \CALLS, \REPEAT, $totalCalls, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f us/call)%s', $cpu, $cpu / $totalCalls * 1000000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
