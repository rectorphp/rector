<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit90\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/3388
 * @changelog https://github.com/sebastianbergmann/phpunit/commit/34a0abd8b56a4a9de83c9e56384f462541a0f939
 *
 * @changelog https://github.com/sebastianbergmann/phpunit/tree/master/src/Runner/Hook
 * @see \Rector\PHPUnit\Tests\PHPUnit90\Rector\Class_\TestListenerToHooksRector\TestListenerToHooksRectorTest
 */
final class TestListenerToHooksRector extends AbstractRector
{
    /**
     * @var array<string, array<class-string|string>>
     */
    private const LISTENER_METHOD_TO_HOOK_INTERFACES = [
        'addIncompleteTest' => ['PHPUnit\\Runner\\AfterIncompleteTestHook', 'executeAfterIncompleteTest'],
        'addRiskyTest' => ['PHPUnit\\Runner\\AfterRiskyTestHook', 'executeAfterRiskyTest'],
        'addSkippedTest' => ['PHPUnit\\Runner\\AfterSkippedTestHook', 'executeAfterSkippedTest'],
        'addError' => ['PHPUnit\\Runner\\AfterTestErrorHook', 'executeAfterTestError'],
        'addFailure' => ['PHPUnit\\Runner\\AfterTestFailureHook', 'executeAfterTestFailure'],
        'addWarning' => ['PHPUnit\\Runner\\AfterTestWarningHook', 'executeAfterTestWarning'],
        # test
        'startTest' => ['PHPUnit\\Runner\\BeforeTestHook', 'executeBeforeTest'],
        'endTest' => ['PHPUnit\\Runner\\AfterTestHook', 'executeAfterTest'],
        # suite
        'startTestSuite' => ['PHPUnit\\Runner\\BeforeFirstTestHook', 'executeBeforeFirstTest'],
        'endTestSuite' => ['PHPUnit\\Runner\\AfterLastTestHook', 'executeAfterLastTest'],
    ];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Refactor "*TestListener.php" to particular "*Hook.php" files', [new CodeSample(<<<'CODE_SAMPLE'
namespace App\Tests;

use PHPUnit\Framework\TestListener;

final class BeforeListHook implements TestListener
{
    public function addError(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addWarning(Test $test, Warning $e, float $time): void
    {
    }

    public function addFailure(Test $test, AssertionFailedError $e, float $time): void
    {
    }

    public function addIncompleteTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addRiskyTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function addSkippedTest(Test $test, \Throwable $t, float $time): void
    {
    }

    public function startTestSuite(TestSuite $suite): void
    {
    }

    public function endTestSuite(TestSuite $suite): void
    {
    }

    public function startTest(Test $test): void
    {
        echo 'start test!';
    }

    public function endTest(Test $test, float $time): void
    {
        echo $time;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
namespace App\Tests;

final class BeforeListHook implements \PHPUnit\Runner\BeforeTestHook, \PHPUnit\Runner\AfterTestHook
{
    public function executeBeforeTest(Test $test): void
    {
        echo 'start test!';
    }

    public function executeAfterTest(Test $test, float $time): void
    {
        echo $time;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * List of nodes this class checks, classes that implement @see \PhpParser\Node
     *
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * Process Node of matched type
     *
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->isObjectType($node, new ObjectType('PHPUnit\\Framework\\TestListener'))) {
            return null;
        }
        foreach ($node->implements as $key => $implement) {
            if (!$this->isName($implement, 'PHPUnit\\Framework\\TestListener')) {
                continue;
            }
            unset($node->implements[$key]);
        }
        foreach ($node->getMethods() as $classMethod) {
            $this->processClassMethod($node, $classMethod);
        }
        return $node;
    }
    private function processClassMethod(Class_ $class, ClassMethod $classMethod) : void
    {
        foreach (self::LISTENER_METHOD_TO_HOOK_INTERFACES as $methodName => $hookClassAndMethod) {
            /** @var string $methodName */
            if (!$this->isName($classMethod, $methodName)) {
                continue;
            }
            // remove empty methods
            if ($classMethod->stmts === [] || $classMethod->stmts === null) {
                $stmtKey = $classMethod->getAttribute(AttributeKey::STMT_KEY);
                unset($class->stmts[$stmtKey]);
            } else {
                $class->implements[] = new FullyQualified($hookClassAndMethod[0]);
                $classMethod->name = new Identifier($hookClassAndMethod[1]);
            }
        }
    }
}
