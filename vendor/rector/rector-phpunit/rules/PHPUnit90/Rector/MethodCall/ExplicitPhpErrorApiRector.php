<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit90\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\PHPUnit\NodeFactory\AssertCallFactory;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/blob/master/ChangeLog-9.0.md
 * @changelog https://github.com/sebastianbergmann/phpunit/commit/1ba2e3e1bb091acda3139f8a9259fa8161f3242d
 *
 * @see \Rector\PHPUnit\Tests\PHPUnit90\Rector\MethodCall\ExplicitPhpErrorApiRector\ExplicitPhpErrorApiRectorTest
 */
final class ExplicitPhpErrorApiRector extends AbstractRector
{
    /**
     * @readonly
     */
    private AssertCallFactory $assertCallFactory;
    /**
     * @readonly
     */
    private TestsNodeAnalyzer $testsNodeAnalyzer;
    /**
     * @var array<string, string>
     */
    private const REPLACEMENTS = ['PHPUnit\\Framework\\TestCase\\Notice' => 'expectNotice', 'PHPUnit\\Framework\\TestCase\\Deprecated' => 'expectDeprecation', 'PHPUnit\\Framework\\TestCase\\Error' => 'expectError', 'PHPUnit\\Framework\\TestCase\\Warning' => 'expectWarning'];
    public function __construct(AssertCallFactory $assertCallFactory, TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->assertCallFactory = $assertCallFactory;
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Use explicit API for expecting PHP errors, warnings, and notices', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectException(\PHPUnit\Framework\TestCase\Deprecated::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Error::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Notice::class);
        $this->expectException(\PHPUnit\Framework\TestCase\Warning::class);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $this->expectDeprecation();
        $this->expectError();
        $this->expectNotice();
        $this->expectWarning();
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [MethodCall::class, StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     * @return null|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    public function refactor(Node $node)
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['expectException'])) {
            return null;
        }
        foreach (self::REPLACEMENTS as $class => $method) {
            $newNode = $this->replaceExceptionWith($node, $class, $method);
            if ($newNode instanceof Node) {
                return $newNode;
            }
        }
        return null;
    }
    /**
     * @param \PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall $node
     * @return null|\PhpParser\Node\Expr\MethodCall|\PhpParser\Node\Expr\StaticCall
     */
    private function replaceExceptionWith($node, string $exceptionClass, string $explicitMethod)
    {
        if ($node->isFirstClassCallable()) {
            return null;
        }
        if (!isset($node->getArgs()[0])) {
            return null;
        }
        $firstArg = $node->getArgs()[0];
        if (!$this->isClassConstReference($firstArg->value, $exceptionClass)) {
            return null;
        }
        return $this->assertCallFactory->createCallWithName($node, $explicitMethod);
    }
    /**
     * Detects "SomeClass::class"
     */
    private function isClassConstReference(Expr $expr, string $className) : bool
    {
        if (!$expr instanceof ClassConstFetch) {
            return \false;
        }
        if (!$this->isName($expr->name, 'class')) {
            return \false;
        }
        return $this->isName($expr->class, $className);
    }
}
