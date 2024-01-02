<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\New_;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\AssignOp;
use PhpParser\Node\Expr\AssignRef;
use PhpParser\Node\Expr\Instanceof_;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Expr\StaticPropertyFetch;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\Expression;
use Rector\NodeFactory\NamedVariableFactory;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Rector\PhpParser\Node\BetterNodeFinder;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/variable_syntax_tweaks#arbitrary_expression_support_for_new_and_instanceof
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\DowngradeArbitraryExpressionsSupportRectorTest
 */
final class DowngradeArbitraryExpressionsSupportRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\NodeFactory\NamedVariableFactory
     */
    private $namedVariableFactory;
    /**
     * @readonly
     * @var \Rector\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    public function __construct(NamedVariableFactory $namedVariableFactory, BetterNodeFinder $betterNodeFinder)
    {
        $this->namedVariableFactory = $namedVariableFactory;
        $this->betterNodeFinder = $betterNodeFinder;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace arbitrary expressions used with new or instanceof', [new CodeSample(<<<'CODE_SAMPLE'
function getObjectClassName() {
    return stdClass::class;
}

$object = new (getObjectClassName());
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function getObjectClassName() {
    return stdClass::class;
}

$className = getObjectClassName();
$object = new $className();
CODE_SAMPLE
)]);
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
     * @return Node\Stmt[]|null|Expression
     */
    public function refactor(Node $node)
    {
        /** @var Assign[] $assigns */
        $assigns = $this->betterNodeFinder->findInstancesOf($node, [Assign::class]);
        if ($assigns !== []) {
            return $this->refactorAssign($assigns, $node);
        }
        /** @var Instanceof_[] $instancesOf */
        $instancesOf = $this->betterNodeFinder->findInstancesOf($node, [Instanceof_::class]);
        if ($instancesOf !== []) {
            return $this->refactorInstanceof($instancesOf[0], $node);
        }
        return null;
    }
    private function isAllowed(Expr $expr) : bool
    {
        return $expr instanceof Variable || $expr instanceof ArrayDimFetch || $expr instanceof PropertyFetch || $expr instanceof StaticPropertyFetch;
    }
    private function isAssign(Expr $expr) : bool
    {
        return $expr instanceof Assign || $expr instanceof AssignRef || $expr instanceof AssignOp;
    }
    private function isBetweenParentheses(Node $node) : bool
    {
        $oldTokens = $this->file->getOldTokens();
        $previousTokenPos = $node->getStartTokenPos() - 1;
        while ($previousTokenPos >= 0) {
            $token = $oldTokens[$previousTokenPos] ?? null;
            --$previousTokenPos;
            if (!isset($token[0])) {
                return $token === '(';
            }
            if (!\in_array($token[0], [\T_COMMENT, \T_WHITESPACE], \true)) {
                return $token === '(';
            }
        }
        return \false;
    }
    /**
     * @param Assign[] $assigns
     * @return Node\Stmt[]|null
     */
    private function refactorAssign(array $assigns, Expression $expression) : ?array
    {
        foreach ($assigns as $assign) {
            if (!$assign->expr instanceof New_ && !$assign->expr instanceof Instanceof_) {
                continue;
            }
            $newOrInstanceof = $assign->expr;
            if (!$newOrInstanceof->class instanceof Expr) {
                continue;
            }
            $isAllowed = $this->isAllowed($newOrInstanceof->class);
            if ($isAllowed && $this->isBetweenParentheses($newOrInstanceof)) {
                continue;
            }
            // mandatory to remove parentheses
            $newOrInstanceof->setAttribute(AttributeKey::ORIGINAL_NODE, null);
            if ($isAllowed) {
                continue;
            }
            if ($this->isAssign($newOrInstanceof->class)) {
                /** @var Assign|AssignRef|AssignOp $exprAssign */
                $exprAssign = $newOrInstanceof->class;
                $variable = $exprAssign->var;
            } else {
                $variable = $this->namedVariableFactory->createVariable('className', $expression);
                $exprAssign = new Assign($variable, $newOrInstanceof->class);
            }
            $newOrInstanceof->class = $variable;
            return [new Expression($exprAssign), $expression];
        }
        return null;
    }
    /**
     * @return Node\Stmt[]|null
     */
    private function refactorInstanceof(Instanceof_ $instanceof, Expression $expression) : ?array
    {
        if (!$instanceof->class instanceof Expr) {
            return null;
        }
        $isAllowed = $this->isAllowed($instanceof->class);
        if ($isAllowed && $this->isBetweenParentheses($instanceof)) {
            return null;
        }
        // mandatory to remove parentheses
        $instanceof->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        $variable = $this->namedVariableFactory->createVariable('className', $expression);
        $exprAssign = new Assign($variable, $instanceof->class);
        $instanceof->class = $variable;
        return [new Expression($exprAssign), $expression];
    }
}
