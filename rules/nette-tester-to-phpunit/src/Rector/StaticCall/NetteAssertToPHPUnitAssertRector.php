<?php

declare(strict_types=1);

namespace Rector\NetteTesterToPHPUnit\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteTesterToPHPUnit\AssertManipulator;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\NetteTesterToPHPUnit\Tests\Rector\Class_\NetteTesterClassToPHPUnitClassRector\NetteTesterPHPUnitRectorTest
 */
final class NetteAssertToPHPUnitAssertRector extends AbstractRector
{
    /**
     * @var AssertManipulator
     */
    private $assertManipulator;

    public function __construct(AssertManipulator $assertManipulator)
    {
        $this->assertManipulator = $assertManipulator;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Migrate Nette/Assert calls to PHPUnit', [
            new CodeSample(
                <<<'CODE_SAMPLE'
use Tester\Assert;

function someStaticFunctions()
{
    Assert::true(10 == 5);
}
CODE_SAMPLE
                ,
                <<<'CODE_SAMPLE'
use Tester\Assert;

function someStaticFunctions()
{
    \PHPUnit\Framework\Assert::assertTrue(10 == 5);
}
CODE_SAMPLE
            ),
        ]);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [StaticCall::class];
    }

    /**
     * @param StaticCall $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isObjectType($node, 'Tester\Assert')) {
            return null;
        }

        return $this->assertManipulator->processStaticCall($node);
    }
}
