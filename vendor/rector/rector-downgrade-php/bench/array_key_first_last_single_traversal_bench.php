<?php

declare (strict_types=1);
namespace RectorPrefix202610;

/**
 * Benchmark for DowngradeArrayKeyFirstLastRector.
 *
 * refactor() used to run ExprInTopStmtMatcher::match() twice per statement - one full subtree
 * traversal to find array_key_first(), then a second full traversal to find array_key_last(). For
 * the overwhelming majority of statements (which contain neither) that is two full traversals of
 * the statement's expression tree where one would do.
 *
 * Matching either name in a single traversal and dispatching on the matched name halves the matcher
 * cost on the common path.
 *
 * This bench drives refactor() over many expression statements that contain neither call - the
 * common case. CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\Expression;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\DowngradePhp73\Rector\FuncCall\DowngradeArrayKeyFirstLastRector;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('STMTS', 300);
\define('REPEAT', 200);
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(DowngradeArrayKeyFirstLastRector::class);
$source = "<?php\n\nnamespace Bench;\n\nfunction run(\$items)\n{\n";
for ($i = 0; $i < \STMTS; ++$i) {
    // statements with funccalls that are neither array_key_first nor array_key_last
    $source .= "    \$result{$i} = array_map('trim', array_filter(\$items, 'is_string'));\n";
}
$source .= "}\n";
$sourceFilePath = \tempnam(\sys_get_temp_dir(), 'bench_') . '.php';
\file_put_contents($sourceFilePath, $source);
$stmts = $testingParser->parseFileToDecoratedNodes($sourceFilePath);
/** @var Expression[] $expressions */
$expressions = $betterNodeFinder->findInstanceOf($stmts, Expression::class);
$cpuSeconds = static function (): float {
    $usage = \getrusage();
    return $usage['ru_utime.tv_sec'] + $usage['ru_utime.tv_usec'] / 1000000 + $usage['ru_stime.tv_sec'] + $usage['ru_stime.tv_usec'] / 1000000;
};
$run = static function () use ($rector, $expressions): void {
    foreach ($expressions as $expression) {
        $rector->refactor($expression);
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
$totalCalls = \REPEAT * \count($expressions);
\printf('statements: %d, repeats: %d, refactor() calls: %d%s', \count($expressions), \REPEAT, $totalCalls, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f us/call)%s', $cpu, $cpu / $totalCalls * 1000000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
