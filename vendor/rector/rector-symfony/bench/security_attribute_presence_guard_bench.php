<?php

declare (strict_types=1);
namespace RectorPrefix202610;

/**
 * Benchmark for SecurityAttributeToIsGrantedAttributeRector.
 *
 * The rule subscribes to Class_ and ClassMethod. refactor() used to call
 * AttributePresenceDetector::detect() - a ReflectionProvider hasClass()/getClass()/isAttributeClass()
 * lookup - on every such node before looking at its attributes, so the reflection ran even for the
 * overwhelming majority of classes and methods that carry no attributes at all.
 *
 * Bailing when the node has no attribute groups skips the reflection lookup entirely for them.
 *
 * This bench drives refactor() over many attribute-less class methods - the common case.
 * CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Symfony\Symfony62\Rector\Class_\SecurityAttributeToIsGrantedAttributeRector;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('METHODS', 300);
\define('REPEAT', 200);
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(SecurityAttributeToIsGrantedAttributeRector::class);
$source = "<?php\n\nnamespace Bench;\n\nclass SomeController\n{\n";
for ($i = 0; $i < \METHODS; ++$i) {
    // plain methods, no attributes - the common case that must bail cheaply
    $source .= "    public function action{$i}() { return {$i}; }\n";
}
$source .= "}\n";
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
$totalCalls = \REPEAT * \count($classMethods);
\printf('methods: %d, repeats: %d, refactor() calls: %d%s', \count($classMethods), \REPEAT, $totalCalls, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f us/call)%s', $cpu, $cpu / $totalCalls * 1000000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
