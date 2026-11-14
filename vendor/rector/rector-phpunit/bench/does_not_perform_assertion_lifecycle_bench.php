<?php

declare (strict_types=1);
namespace RectorPrefix202611;

/**
 * Benchmark for AddDoesNotPerformAssertionToNonAssertingTestRector on test classes with deep parent chains.
 *
 * Each test class has parent lifecycle hooks (setUp(), tearDown()) and parent::test*() calls. Resolving these
 * via AstResolver parses and decorates parent files on first use, so this bench measures one cold pass, as in a real run.
 *
 * Run on both branches to compare. CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\ClassMethod;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('CHAINS', 10);
\define('DEPTH', 4);
\define('TESTS_PER_CHAIN', 30);
\define('TEST_METHODS', 5);
\define('FILLER_METHODS', 15);
\define('MAX_CPU_SECONDS', 1.0);
// fails in CI above this
$directory = \sys_get_temp_dir() . '/rector_phpunit_bench_' . \getmypid();
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
for ($chain = 0; $chain < \CHAINS; ++$chain) {
    $parentClassName = 'PHPUnit\Framework\TestCase';
    for ($level = 0; $level < \DEPTH; ++$level) {
        $className = \sprintf('AbstractChain%dLevel%d', $chain, $level);
        $methods = "    protected function setUp(): void\n    {\n        parent::setUp();\n        \$this->prepare{$level}();\n    }\n\n";
        $methods .= "    protected function tearDown(): void\n    {\n        \$this->cleanup = null;\n        parent::tearDown();\n    }\n\n";
        $methods .= "    public function testInherited(): void\n    {\n        \$value = {$level};\n    }\n\n";
        $methods .= "    private function prepare{$level}(): void\n    {\n        \$this->ready = true;\n    }\n\n";
        for ($filler = 0; $filler < \FILLER_METHODS; ++$filler) {
            $methods .= "    protected function helper{$filler}(array \$items): array\n    {\n        return array_map(fn (\$item) => \$item * {$filler}, \$items);\n    }\n\n";
        }
        $writeClass($className, "abstract class {$className} extends {$parentClassName}\n{\n    protected \$ready;\n    protected \$cleanup;\n\n{$methods}}\n");
        $parentClassName = $className;
    }
    for ($test = 0; $test < \TESTS_PER_CHAIN; ++$test) {
        $className = \sprintf('Chain%dTest%d', $chain, $test);
        $methods = "    public function testInherited(): void\n    {\n        parent::testInherited();\n    }\n\n";
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
$classMethods = [];
foreach ($testFilePaths as $testFilePath) {
    $stmts = $testingParser->parseFileToDecoratedNodes($testFilePath);
    $classMethods = \array_merge($classMethods, $betterNodeFinder->findInstanceOf($stmts, ClassMethod::class));
}
$cpuSeconds = static function (): float {
    $usage = \getrusage();
    return $usage['ru_utime.tv_sec'] + $usage['ru_utime.tv_usec'] / 1000000 + $usage['ru_stime.tv_sec'] + $usage['ru_stime.tv_usec'] / 1000000;
};
$wallStart = \microtime(\true);
$cpuStart = $cpuSeconds();
$changedCount = 0;
foreach ($classMethods as $classMethod) {
    if ($rector->refactor($classMethod) instanceof ClassMethod) {
        ++$changedCount;
    }
}
$cpu = $cpuSeconds() - $cpuStart;
$wall = \microtime(\true) - $wallStart;
\array_map('unlink', \glob($directory . '/*.php'));
@\rmdir($directory);
\printf('test classes: %d, test methods: %d, changed: %d%s', \count($testFilePaths), \count($classMethods), $changedCount, \PHP_EOL);
\printf('CPU:  %.4f s  (%.3f ms/method)%s', $cpu, $cpu / \count($classMethods) * 1000, \PHP_EOL);
\printf('wall: %.4f s%s', $wall, \PHP_EOL);
if ($cpu > \MAX_CPU_SECONDS) {
    \printf('CPU %.4f s is over the %.1f s limit%s', $cpu, \MAX_CPU_SECONDS, \PHP_EOL);
    exit(1);
}
