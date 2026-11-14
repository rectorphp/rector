<?php

declare (strict_types=1);
namespace RectorPrefix202611;

/**
 * Benchmark for AddDoesNotPerformAssertionToNonAssertingTestRector on test classes with own lifecycle methods.
 *
 * Every test class has its own setUp() and tearDown(), every other one asserts in setUp().
 * Run on both branches to compare. CPU (user+sys) is the reliable metric; wall is noisy.
 */
use PhpParser\Node\Stmt\Class_;
use Rector\DependencyInjection\LazyContainerFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\PHPUnit\PHPUnit60\Rector\ClassMethod\AddDoesNotPerformAssertionToNonAssertingTestRector;
use Rector\Testing\TestingParser\TestingParser;
require __DIR__ . '/../vendor/autoload.php';
\define('TEST_CLASSES', 300);
\define('TEST_METHODS', 6);
\define('MAX_CPU_SECONDS', 1.0);
// fails in CI above this
$directory = \sys_get_temp_dir() . '/rector_phpunit_own_lifecycle_bench_' . \getmypid();
@\mkdir($directory);
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
for ($test = 0; $test < \TEST_CLASSES; ++$test) {
    $isAsserting = $test % 2 === 0;
    $className = \sprintf('%sTest%d', $isAsserting ? 'Asserting' : 'NonAsserting', $test);
    $setUpBody = $isAsserting ? '$this->assertTrue(true);' : '$this->ready = true;';
    $methods = "    protected function setUp(): void\n    {\n        parent::setUp();\n        {$setUpBody}\n    }\n\n";
    $methods .= "    protected function tearDown(): void\n    {\n        \$this->ready = null;\n        parent::tearDown();\n    }\n\n";
    for ($testMethod = 0; $testMethod < \TEST_METHODS; ++$testMethod) {
        $methods .= "    public function testSomething{$testMethod}(): void\n    {\n        \$value = {$testMethod};\n    }\n\n";
    }
    $filePath = $directory . '/' . $className . '.php';
    \file_put_contents($filePath, "<?php\n\nnamespace Bench;\n\nfinal class {$className} extends \\PHPUnit\\Framework\\TestCase\n{\n    protected \$ready;\n\n{$methods}}\n");
    $testFilePaths[] = $filePath;
}
$rectorConfig = (new LazyContainerFactory())->create();
$rectorConfig->boot();
/** @var TestingParser $testingParser */
$testingParser = $rectorConfig->make(TestingParser::class);
/** @var BetterNodeFinder $betterNodeFinder */
$betterNodeFinder = $rectorConfig->make(BetterNodeFinder::class);
$rector = $rectorConfig->make(AddDoesNotPerformAssertionToNonAssertingTestRector::class);
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
