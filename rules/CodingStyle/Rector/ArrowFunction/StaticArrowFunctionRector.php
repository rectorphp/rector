<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr\ArrowFunction;
use Rector\CodingStyle\Guard\StaticGuard;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\CodingStyle\Rector\ArrowFunction\StaticArrowFunctionRector\StaticArrowFunctionRectorTest
 */
final class StaticArrowFunctionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\CodingStyle\Guard\StaticGuard
     */
    private $staticGuard;
    public function __construct(StaticGuard $staticGuard)
    {
        $this->staticGuard = $staticGuard;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Changes ArrowFunction to be static when possible', [new CodeSample(<<<'CODE_SAMPLE'
fn (): string => 'test';
CODE_SAMPLE
, <<<'CODE_SAMPLE'
static fn (): string => 'test';
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$this->staticGuard->isLegal($node)) {
            return null;
        }
        $node->static = \true;
        return $node;
    }
}
