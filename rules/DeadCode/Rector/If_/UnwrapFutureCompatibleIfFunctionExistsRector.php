<?php

declare (strict_types=1);
namespace Rector\DeadCode\Rector\If_;

use PhpParser\Node;
use PhpParser\Node\Arg;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\If_;
use PHPStan\Type\ObjectType;
use Rector\Core\NodeManipulator\IfManipulator;
use Rector\Core\Rector\AbstractRector;
use Rector\DeadCode\FeatureSupport\FunctionSupportResolver;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Tests\DeadCode\Rector\If_\UnwrapFutureCompatibleIfFunctionExistsRector\UnwrapFutureCompatibleIfFunctionExistsRectorTest
 */
final class UnwrapFutureCompatibleIfFunctionExistsRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\DeadCode\FeatureSupport\FunctionSupportResolver
     */
    private $functionSupportResolver;
    /**
     * @readonly
     * @var \Rector\Core\NodeManipulator\IfManipulator
     */
    private $ifManipulator;
    public function __construct(\Rector\DeadCode\FeatureSupport\FunctionSupportResolver $functionSupportResolver, \Rector\Core\NodeManipulator\IfManipulator $ifManipulator)
    {
        $this->functionSupportResolver = $functionSupportResolver;
        $this->ifManipulator = $ifManipulator;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Remove functions exists if with else for always existing', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        if (function_exists('session_abort')) {
            session_abort();
        } else {
            session_write_close();
        }
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        // session locking trough other addons
        session_abort();
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
        return [\PhpParser\Node\Stmt\If_::class];
    }
    /**
     * @param If_ $node
     * @return null|Stmt[]
     */
    public function refactor(\PhpParser\Node $node) : ?array
    {
        if ($this->shouldSkip($node)) {
            return null;
        }
        $match = $this->ifManipulator->isIfOrIfElseWithFunctionCondition($node, 'function_exists');
        if (!$match) {
            return null;
        }
        /** @var FuncCall $funcCall */
        $funcCall = $node->cond;
        if (!isset($funcCall->args[0])) {
            return null;
        }
        if (!$funcCall->args[0] instanceof \PhpParser\Node\Arg) {
            return null;
        }
        $functionToExistName = $this->valueResolver->getValue($funcCall->args[0]->value);
        if (!\is_string($functionToExistName)) {
            return null;
        }
        if (!$this->functionSupportResolver->isFunctionSupported($functionToExistName)) {
            return null;
        }
        return $node->stmts;
    }
    private function shouldSkip(\PhpParser\Node\Stmt\If_ $if) : bool
    {
        $classLike = $this->betterNodeFinder->findParentType($if, \PhpParser\Node\Stmt\ClassLike::class);
        if (!$classLike instanceof \PhpParser\Node\Stmt\ClassLike) {
            return \false;
        }
        // skip rector rules, as they decided if function exists in that particular projects
        return $this->isObjectType($classLike, new \PHPStan\Type\ObjectType('Rector\\Core\\Contract\\Rector\\RectorInterface'));
    }
}
