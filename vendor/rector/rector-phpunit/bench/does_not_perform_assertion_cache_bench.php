<?php

declare (strict_types=1);
namespace RectorPrefix202611;

/**
 * Benchmark for AddDoesNotPerformAssertionToNonAssertingTestRector on many test classes sharing helper methods.
 *
 * Each test method calls a helper declared in an abstract parent, and one calls "parent::setUp()" that is only
 * declared in TestCase. This measures the containsAssertCall() cache and the "parent::" resolution.
 *
 * Run on both branches to compare. CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\Class_;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('PARENTS', 5);
\define('TESTS_PER_PARENT', 60);
\define('TEST_METHODS', 8);
\define('HELPER_STATEMENTS', 5);
\define('MAX_CPU_SECONDS', 1.0);
// fails in CI above this
$directory = \sys_get_temp_dir() . '/rector_phpunit_cache_bench_' . \getmypid();
@\mkdir($directory);
$writeClass = static function (string $className, string $body) use ($directory): string {
    $filePath = $directory . '/' . $className . '.php';
    \file_put_contents($filePath, "<?php\n\nnamespace Bench;\n\n" . $body);
    return $filePath;
};
// make generated classes visible to PHPStan reflection
\spl_autoload_register(static function (string $class) use ($directory): void {
    if (\strncmp($class, 'Bench\\', \strlen('Bench\\')) !== 0) {
        return;
    }
    $filePath = $directory . '/' . \substr($class, 6) . '.php';
    if (\is_file($filePath)) {
        require_once $filePath;
    }
});
$testFilePaths = [];
for ($parent = 0; $parent < \PARENTS; ++$parent) {
    $parentClassName = \sprintf('AbstractCacheParent%d', $parent);
    $methods = '';
    for ($helper = 0; $helper < \TEST_METHODS; ++$helper) {
        $statements = '';
        for ($statement = 0; $statement < \HELPER_STATEMENTS; ++$statement) {
            $statements .= "        \$items = array_map(fn (\$item) => \$item + {$statement}, \$items);\n";
        }
        $methods .= "    protected function helper{$helper}(array \$items): array\n    {\n{$statements}        return \$items;\n    }\n\n";
    }
    $writeClass($parentClassName, "abstract class {$parentClassName} extends \\PHPUnit\\Framework\\TestCase\n{\n{$methods}}\n");
    for ($test = 0; $test < \TESTS_PER_PARENT; ++$test) {
        $className = \sprintf('CacheParent%dTest%d', $parent, $test);
        $methods = "    public function testRestart(): void\n    {\n        parent::setUp();\n    }\n\n";
        for ($testMethod = 0; $testMethod < \TEST_METHODS; ++$testMethod) {
            $methods .= "    public function testSomething{$testMethod}(): void\n    {\n        \$value = \$this->helper{$testMethod}([1, 2, 3]);\n    }\n\n";
        }
        $testFilePaths[] = $writeClass($className, "final class {$className} extends {$parentClassName}\n{\n{$methods}}\n");
    }
}
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(AddDoesNotPerformAssertionToNonAssertingTestRector::class);
// parse test files up front, parent files are left for the rule to resolve
$classes = [];
foreach ($testFilePaths as $testFilePath) {
    $stmts = $testingParser->parseFileToDecoratedNodes($testFilePath);
    $classes = \array_merge($classes, $betterNodeFinder->findInstanceOf($stmts, Class_::class));
}
$cpuSeconds = static function (): float {
    $usage = \getrusage();
    return $usage['ru_utime.tv_sec'] + $usage['ru_utime.tv_usec'] / 1000000 + $usage['ru_stime.tv_sec'] + $usage['ru_stime.tv_usec'] / 1000000;
};
$wallStart = \microtime(\true);
$cpuStart = $cpuSeconds();
$changedCount = 0;
foreach ($classes as $class) {
    if ($rector->refactor($class) instanceof Class_) {
        ++$changedCount;
    }
}
$cpu = $cpuSeconds() - $cpuStart;
$wall = \microtime(\true) - $wallStart;
\array_map('unlink', \glob($directory . '/*.php'));
@\rmdir($directory);
\printf('test classes: %d, changed: %d%s', \count($classes), $changedCount, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f ms/class)%s', $cpu, $cpu / \count($classes) * 1000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
if ($cpu > \MAX_CPU_SECONDS) {
    \printf('CPU %.4f s is over the %.1f s limit%s', $cpu, \MAX_CPU_SECONDS, \PHP_EOL);
    exit(1);
}
