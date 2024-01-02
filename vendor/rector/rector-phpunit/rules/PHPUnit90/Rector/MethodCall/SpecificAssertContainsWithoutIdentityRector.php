<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PHPUnit90\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\StringType;
use Rector\PhpParser\Node\Value\ValueResolver;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/sebastianbergmann/phpunit/issues/3426
 * @see \Rector\PHPUnit\Tests\PHPUnit90\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\SpecificAssertContainsWithoutIdentityRectorTest
 */
final class SpecificAssertContainsWithoutIdentityRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\Value\ValueResolver
     */
    private $valueResolver;
    /**
     * @var array<string, array<string, string>>
     */
    private const OLD_METHODS_NAMES_TO_NEW_NAMES = ['string' => ['assertContains' => 'assertContainsEquals', 'assertNotContains' => 'assertNotContainsEquals']];
    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer, ValueResolver $valueResolver)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
        $this->valueResolver = $valueResolver;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Change assertContains()/assertNotContains() with non-strict comparison to new specific alternatives', [new CodeSample(<<<'CODE_SAMPLE'
final class SomeTest extends \PHPUnit\Framework\TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContains(new \stdClass(), $objects, 'message', false, false);
        $this->assertNotContains(new \stdClass(), $objects, 'message', false, false);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
final class SomeTest extends TestCase
{
    public function test()
    {
        $objects = [ new \stdClass(), new \stdClass(), new \stdClass() ];
        $this->assertContainsEquals(new \stdClass(), $objects, 'message');
        $this->assertNotContainsEquals(new \stdClass(), $objects, 'message');
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
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }
        if ($node->isFirstClassCallable()) {
            return null;
        }
        // when second argument is string: do nothing
        $secondArgType = $this->getType($node->getArgs()[1]->value);
        if ($secondArgType instanceof StringType) {
            return null;
        }
        //when less then 5 arguments given: do nothing
        if (!isset($node->getArgs()[4])) {
            return null;
        }
        $fourthArg = $node->getArgs()[4];
        //when 5th argument check identity is true: do nothing
        if ($this->valueResolver->isValue($fourthArg->value, \true)) {
            return null;
        }
        /* here we search for element of array without identity check  and we can replace functions */
        $methodName = $this->getName($node->name);
        $node->name = new Identifier(self::OLD_METHODS_NAMES_TO_NEW_NAMES['string'][$methodName]);
        unset($node->args[3], $node->args[4]);
        return $node;
    }
}
