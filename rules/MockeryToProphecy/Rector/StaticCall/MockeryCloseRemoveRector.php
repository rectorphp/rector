<?php

declare(strict_types=1);

namespace Rector\MockeryToProphecy\Rector\StaticCall;

use PhpParser\Node;
use PhpParser\Node\Expr\StaticCall;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\PHPUnit\NodeAnalyzer\TestsNodeAnalyzer;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;

/**
 * @see \Rector\Tests\MockeryToProphecy\Rector\StaticCall\MockeryToProphecyRector\MockeryToProphecyRectorTest
 */
final class MockeryCloseRemoveRector extends AbstractRector
{
    public function __construct(
        private TestsNodeAnalyzer $testsNodeAnalyzer
    ) {
    }

    /**
     * @return array<class-string<Node>>
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
        if (! $this->testsNodeAnalyzer->isInTestClass($node)) {
            return null;
        }

        $callerType = $this->nodeTypeResolver->resolve($node->class);
        if (! $callerType->isSuperTypeOf(new ObjectType('Mockery'))->yes()) {
            return null;
        }

        if (! $this->isName($node->name, 'close')) {
            return null;
        }

        $this->removeNode($node);

        return null;
    }

    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition(
            'Removes mockery close from test classes',
            [
                new CodeSample(
                    <<<'CODE_SAMPLE'
public function tearDown() : void
{
    \Mockery::close();
}
CODE_SAMPLE
,
                    <<<'CODE_SAMPLE'
public function tearDown() : void
{
}
CODE_SAMPLE
                ),
            ]
        );
    }
}
