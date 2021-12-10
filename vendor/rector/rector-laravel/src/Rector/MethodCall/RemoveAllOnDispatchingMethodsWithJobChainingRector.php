<?php

declare (strict_types=1);
namespace Rector\Laravel\Rector\MethodCall;

use PhpParser\Node;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Identifier;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Laravel\Tests\Rector\MethodCall\RemoveAllOnDispatchingMethodsWithJobChainingRector\RemoveAllOnDispatchingMethodsWithJobChainingRectorTest
 */
final class RemoveAllOnDispatchingMethodsWithJobChainingRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @var string
     */
    private const DISPATCH = 'dispatch';
    /**
     * @var array<string, string>
     */
    private const SWAPPED_METHODS = ['allOnQueue' => 'onQueue', 'allOnConnection' => 'onConnection'];
    /**
     * @var \Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer
     */
    private $fluentChainMethodCallNodeAnalyzer;
    public function __construct(\Rector\Defluent\NodeAnalyzer\FluentChainMethodCallNodeAnalyzer $fluentChainMethodCallNodeAnalyzer)
    {
        $this->fluentChainMethodCallNodeAnalyzer = $fluentChainMethodCallNodeAnalyzer;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove allOnQueue() and allOnConnection() methods used with job chaining, use the onQueue() and onConnection() methods instead.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
Job::withChain([
    new ChainJob(),
])
    ->dispatch()
    ->allOnConnection('redis')
    ->allOnQueue('podcasts');
CODE_SAMPLE
, <<<'CODE_SAMPLE'
Job::withChain([
    new ChainJob(),
])
    ->onQueue('podcasts')
    ->onConnection('redis')
    ->dispatch();
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [\PhpParser\Node\Expr\MethodCall::class];
    }
    /**
     * @param MethodCall $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$this->isNames($node->name, \array_keys(self::SWAPPED_METHODS))) {
            return null;
        }
        $rootExpr = $this->fluentChainMethodCallNodeAnalyzer->resolveRootExpr($node);
        if (!$this->isObjectType($rootExpr, new \PHPStan\Type\ObjectType('Illuminate\\Foundation\\Bus\\Dispatchable'))) {
            return null;
        }
        // Note that this change only affects code using the withChain method.
        $callerNode = $rootExpr->getAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::PARENT_NODE);
        if (!$callerNode instanceof \PhpParser\Node\Expr\StaticCall) {
            return null;
        }
        if (!$this->isName($callerNode->name, 'withChain')) {
            return null;
        }
        $names = $this->fluentChainMethodCallNodeAnalyzer->collectMethodCallNamesInChain($node);
        if (!\in_array(self::DISPATCH, $names, \true)) {
            return null;
        }
        // These methods should be called before calling the dispatch method.
        $end = $node->var;
        $current = $node->var;
        while ($current instanceof \PhpParser\Node\Expr\MethodCall) {
            if ($this->isName($current->name, self::DISPATCH)) {
                $var = $current->var;
                $current->var = $node;
                $node->name = new \PhpParser\Node\Identifier(self::SWAPPED_METHODS[$this->getName($node->name)]);
                $node->var = $var;
                break;
            }
            $current = $current->var;
        }
        return $end;
    }
}
