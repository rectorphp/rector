<?php

declare (strict_types=1);
namespace Rector\PHPUnit\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\StringType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see https://github.com/sebastianbergmann/phpunit/issues/3426
 * @see \Rector\PHPUnit\Tests\Rector\MethodCall\SpecificAssertContainsWithoutIdentityRector\SpecificAssertContainsWithoutIdentityRectorTest
 */
final class SpecificAssertContainsWithoutIdentityRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var array<string, array<string, string>>
     */
    private const OLD_METHODS_NAMES_TO_NEW_NAMES = ['string' => ['assertContains' => 'assertContainsEquals', 'assertNotContains' => 'assertNotContainsEquals']];
    /**
     * @readonly
     * @var \Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;
    public function __construct(\Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Change assertContains()/assertNotContains() with non-strict comparison to new specific alternatives', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\MethodCall::class, \PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param MethodCall|StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodCallNames($node, ['assertContains', 'assertNotContains'])) {
            return null;
        }
        // when second argument is string: do nothing
        $secondArgType = $this->getType($node->args[1]->value);
        if ($secondArgType instanceof \PHPStan\Type\StringType) {
            return null;
        }
        //when less then 5 arguments given: do nothing
        if (!isset($node->args[4])) {
            return null;
        }
        if ($node->args[4]->value === null) {
            return null;
        }
        //when 5th argument check identity is true: do nothing
        if ($this->valueResolver->isValue($node->args[4]->value, \true)) {
            return null;
        }
        /* here we search for element of array without identity check  and we can replace functions */
        $methodName = $this->getName($node->name);
        $node->name = new \PhpParser\Node\Identifier(self::OLD_METHODS_NAMES_TO_NEW_NAMES['string'][$methodName]);
        unset($node->args[3], $node->args[4]);
        return $node;
    }
}
