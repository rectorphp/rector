<?php

declare (strict_types=1);
namespace Rector\NetteToSymfony\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteToSymfony\AssertManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\NetteToSymfony\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\NetteTesterClassToPHPUnitClassRectorTest
 */
final class NetteAssertToPHPUnitAssertRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var AssertManipulator
     */
    private $assertManipulator;
    public function __construct(\Rector\NetteToSymfony\AssertManipulator $assertManipulator)
    {
        $this->assertManipulator = $assertManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Migrate Nette/Assert calls to PHPUnit', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
use Tester\Assert;

function someStaticFunctions()
{
    Assert::true(10 == 5);
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Tester\Assert;

function someStaticFunctions()
{
    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\StaticCall::class];
    }
    /**
     * @param StaticCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isObjectType($node->class, new \PHPStan\Type\ObjectType('Tester\\Assert'))) {
            return null;
        }
        return $this->assertManipulator->processStaticCall($node);
    }
}
