<?php

declare (strict_types=1);
namespace Rector\Removing\Rector\FuncCall;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeTraverser;
use Rector\Contract\Rector\ConfigurableRectorInterface;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\ConfiguredCodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
use RectorPrefix202407\Webmozart\Assert\Assert;
/**
 * @see \Rector\Tests\Removing\Rector\FuncCall\RemoveFuncCallRector\RemoveFuncCallRectorTest
 */
final class RemoveFuncCallRector extends AbstractRector implements ConfigurableRectorInterface
{
    /**
     * @var string[]
     */
    private $removedFunctions = [];
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Remove function', [new ConfiguredCodeSample(<<<'CODE_SAMPLE'
$x = 'something';
var_dump($x);
CODE_SAMPLE
, <<<'CODE_SAMPLE'
$x = 'something';
CODE_SAMPLE
, ['var_dump'])]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Expression::class];
    }
    /**
     * @param Expression $node
     */
    public function refactor(Node $node) : ?int
    {
        $expr = $node->expr;
        if (!$expr instanceof FuncCall) {
            return null;
        }
        foreach ($this->removedFunctions as $removedFunction) {
            if (!$this->isName($expr->name, $removedFunction)) {
                continue;
            }
            return NodeTraverser::REMOVE_NODE;
        }
        return null;
    }
    /**
     * @param mixed[] $configuration
     */
    public function configure(array $configuration) : void
    {
        Assert::allString($configuration);
        $this->removedFunctions = $configuration;
    }
}
