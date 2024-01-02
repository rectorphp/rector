<?php

declare (strict_types=1);
namespace Rector\DowngradePhp74\Rector\ArrowFunction;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrowFunction;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Closure;
use PhpParser\Node\Expr\ClosureUse;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Return_;
use PhpParser\Node\Stmt\Throw_;
use PHPStan\Analyser\Scope;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\Php72\NodeFactory\AnonymousFunctionFactory;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://www.php.net/manual/en/functions.arrow.php
 *
 * @see \Rector\Tests\DowngradePhp74\Rector\ArrowFunction\ArrowFunctionToAnonymousFunctionRector\ArrowFunctionToAnonymousFunctionRectorTest
 */
final class ArrowFunctionToAnonymousFunctionRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Php72\NodeFactory\AnonymousFunctionFactory
     */
    private $anonymousFunctionFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(AnonymousFunctionFactory $anonymousFunctionFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->anonymousFunctionFactory = $anonymousFunctionFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace arrow functions with anonymous functions', [new CodeSample(<<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = fn($matches) => $delimiter . strtolower($matches[1]);
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
class SomeClass
{
    public function run()
    {
        $delimiter = ",";
        $callable = function ($matches) use ($delimiter) {
            return $delimiter . strtolower($matches[1]);
        };
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
        return [ArrowFunction::class];
    }
    /**
     * @param ArrowFunction $node
     */
    public function refactor(Node $node) : Closure
    {
        $stmts = [new Return_($node->expr)];
        $anonymousFunctionFactory = $this->anonymousFunctionFactory->create($node->params, $stmts, $node->returnType, $node->static);
        if ($node->expr instanceof Assign && $node->expr->expr instanceof Variable) {
            $isFound = (bool) $this->betterNodeFinder->findFirst($anonymousFunctionFactory->uses, function (Node $subNode) use($node) : bool {
                return $subNode instanceof Variable && $this->nodeComparator->areNodesEqual($subNode, $node->expr->expr);
            });
            if (!$isFound) {
                $anonymousFunctionFactory->uses[] = new ClosureUse($node->expr->expr);
            }
        }
        // downgrade "return throw"
        $this->traverseNodesWithCallable($anonymousFunctionFactory, static function (Node $node) : ?Throw_ {
            if (!$node instanceof Return_) {
                return null;
            }
            if (!$node->expr instanceof Node\Expr\Throw_) {
                return null;
            }
            $throw = $node->expr;
            // throw expr to throw stmts
            return new Throw_($throw->expr);
        });
        $this->appendUsesFromInsertedVariable($node->expr, $anonymousFunctionFactory);
        return $anonymousFunctionFactory;
    }
    private function appendUsesFromInsertedVariable(Expr $expr, Closure $anonymousFunctionFactory) : void
    {
        $this->traverseNodesWithCallable($expr, function (Node $subNode) use($anonymousFunctionFactory) {
            if (!$subNode instanceof Variable) {
                return null;
            }
            $variableName = $this->getName($subNode);
            if ($variableName === null) {
                return null;
            }
            if ($subNode->hasAttribute(AttributeKey::ORIGINAL_NODE)) {
                return null;
            }
            $scope = $subNode->getAttribute(AttributeKey::SCOPE);
            if (!$scope instanceof Scope) {
                return null;
            }
            if (!$scope->hasVariableType($variableName)->yes()) {
                $anonymousFunctionFactory->uses[] = new ClosureUse(new Variable($variableName));
            }
        });
    }
}
