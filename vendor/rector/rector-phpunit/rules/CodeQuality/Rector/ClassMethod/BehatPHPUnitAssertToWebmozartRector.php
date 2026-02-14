<?php

declare (strict_types=1);
namespace Rector\PHPUnit\CodeQuality\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt\ClassMethod;
use PHPStan\Reflection\ReflectionProvider;
use Rector\PHPStan\ScopeFetcher;
use Rector\PHPUnit\Enum\BehatClassName;
use Rector\PHPUnit\Enum\PHPUnitClassName;
use Rector\PHPUnit\Enum\WebmozartClassName;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\PHPUnit\Tests\CodeQuality\Rector\ClassMethod\BehatPHPUnitAssertToWebmozartRector\BehatPHPUnitAssertToWebmozartRectorTest
 */
final class BehatPHPUnitAssertToWebmozartRector extends AbstractRector
{
    /**
     * @readonly
     */
    private ReflectionProvider $reflectionProvider;
    /**
     * @var array<string, string>
     */
    private const PHPUNIT_TO_WEBMOZART_METHODS = [
        // Boolean
        'assertTrue' => 'true',
        'assertFalse' => 'false',
        // Null / empty
        'assertNull' => 'null',
        'assertNotNull' => 'notNull',
        'assertEmpty' => 'isEmpty',
        'assertNotEmpty' => 'notEmpty',
        // Type checks
        'assertIsString' => 'string',
        'assertIsInt' => 'integer',
        'assertIsFloat' => 'float',
        'assertIsBool' => 'boolean',
        'assertIsArray' => 'isArray',
        'assertIsObject' => 'object',
        'assertIsCallable' => 'isCallable',
        'assertIsResource' => 'resource',
        'assertIsIterable' => 'isIterable',
        'assertInstanceOf' => 'isInstanceOf',
        // array
        'assertContains' => 'oneOf',
        'assertNotContains' => 'notOneOf',
        // Comparison / equality
        'assertSame' => 'same',
        'assertNotSame' => 'notSame',
        'assertEquals' => 'eq',
        'assertNotEquals' => 'notEq',
        'assertGreaterThan' => 'greaterThan',
        'assertGreaterThanOrEqual' => 'greaterThanEq',
        'assertLessThan' => 'lessThan',
        'assertLessThanOrEqual' => 'lessThanEq',
        // Strings
        'assertStringContainsString' => 'contains',
        'assertStringNotContainsString' => 'notContains',
        'assertStringStartsWith' => 'startsWith',
        'assertStringStartsNotWith' => 'notStartsWith',
        'assertStringEndsWith' => 'endsWith',
        'assertStringEndsNotWith' => 'notEndsWith',
        'assertMatchesRegularExpression' => 'regex',
        'assertDoesNotMatchRegularExpression' => 'notRegex',
        // Bool
        'assertNotTrue' => 'false',
        'assertNotFalse' => 'true',
        // Arrays / count
        'assertCount' => 'count',
        'assertArrayHasKey' => 'keyExists',
        'assertArrayNotHasKey' => 'keyNotExists',
        // Misc / less direct
        'assertFileExists' => 'fileExists',
        'assertFileIsReadable' => 'readable',
        'assertDirectoryExists' => 'directory',
        // Instance of
        'assertNotInstanceOf' => 'notInstanceOf',
    ];
    /**
     * @var string[]
     */
    private const FLIPPED_ARGS = ['assertSame', 'assertNotSame', 'assertEquals', 'assertNotEquals', 'assertGreaterThan', 'assertGreaterThanOrEqual', 'assertLessThan', 'assertLessThanOrEqual', 'assertCount', 'assertInstanceOf', 'assertNotInstanceOf', 'assertArrayHasKey', 'assertArrayNotHasKey', 'assertStringContainsString', 'assertStringStartsWith', 'assertMatchesRegularExpression', 'assertDoesNotMatchRegularExpression'];
    public function __construct(ReflectionProvider $reflectionProvider)
    {
        $this->reflectionProvider = $reflectionProvider;
    }
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Change PHPUnit assert in Behat context files to Webmozart Assert, as first require a TestCase instance', [new CodeSample(<<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;
use PHPUnit\Framework\Assert;

final class SomeContext implements Context
{
    public function someMethod()
    {
        Assert::assertSame('expected', 'actual');
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Behat\Behat\Context\Context;
use Webmozart\Assert\Assert;

final class SomeContext implements Context
{
    public function someMethod()
    {
        Assert::same('actual', 'expected');
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string>
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }
    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?ClassMethod
    {
        $scope = ScopeFetcher::fetch($node);
        if (!$scope->isInClass()) {
            return null;
        }
        $classReflection = $scope->getClassReflection();
        if (!$classReflection->is(BehatClassName::CONTEXT)) {
            return null;
        }
        if (!$this->reflectionProvider->hasClass(WebmozartClassName::ASSERT)) {
            return null;
        }
        $hasChanged = \false;
        $this->traverseNodesWithCallable($node, function (Node $node) use (&$hasChanged): ?StaticCall {
            if (!$node instanceof StaticCall) {
                return null;
            }
            if (!$this->isName($node->class, PHPUnitClassName::ASSERT)) {
                return null;
            }
            $phpunitMethodName = $this->getName($node->name);
            if ($phpunitMethodName === null) {
                return null;
            }
            // changed method name
            $webmozartMethodName = self::PHPUNIT_TO_WEBMOZART_METHODS[$phpunitMethodName] ?? null;
            if ($webmozartMethodName === null) {
                return null;
            }
            if (in_array($phpunitMethodName, self::FLIPPED_ARGS, \true) && count($node->args) >= 2) {
                // flip first 2 args
                $temp = $node->args[0];
                $node->args[0] = $node->args[1];
                $node->args[1] = $temp;
            }
            $node->class = new FullyQualified(WebmozartClassName::ASSERT);
            $node->name = new Identifier($webmozartMethodName);
            $hasChanged = \true;
            return $node;
        });
        if (!$hasChanged) {
            return null;
        }
        return $node;
    }
}
