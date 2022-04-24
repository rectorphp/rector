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
use Rector\Core\PhpParser\Node\NamedVariableFactory;
use Rector\Core\Rector\AbstractRector;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/variable_syntax_tweaks#arbitrary_expression_support_for_new_and_instanceof
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\DowngradeArbitraryExpressionsSupportRectorTest
 */
final class DowngradeArbitraryExpressionsSupportRector extends \Rector\Core\Rector\AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(\Rector\Core\PhpParser\Node\NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    public function getRuleDefinition() : \Symplify\RuleDocGenerator\ValueObject\RuleDefinition
    {
        return new \Symplify\RuleDocGenerator\ValueObject\RuleDefinition('Replace arbitrary expressions used with new or instanceof.', [new \Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample(<<<'CODE_SAMPLE'
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
        return [\PhpParser\Node\Expr\Instanceof_::class, \PhpParser\Node\Expr\New_::class];
    }
    /**
     * @param Instanceof_|New_ $node
     */
    public function refactor(\PhpParser\Node $node) : ?\PhpParser\Node
    {
        if (!$node->class instanceof \PhpParser\Node\Expr) {
            return null;
        }
        $isAllowed = $this->isAllowed($node->class);
        $toSkip = $isAllowed && $this->isBetweenParentheses($node);
        if ($toSkip) {
            return null;
        }
        // mandatory to remove parentheses
        $node->setAttribute(\Rector\NodeTypeResolver\Node\AttributeKey::ORIGINAL_NODE, null);
        if ($isAllowed) {
            return $node;
        }
        if ($this->isAssign($node->class)) {
            /** @var Assign|AssignRef|AssignOp $assign */
            $assign = $node->class;
            $variable = $assign->var;
        } else {
            $variable = $this->namedVariableFactory->createVariable($node, 'className');
            $assign = new \PhpParser\Node\Expr\Assign($variable, $node->class);
        }
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node);
        $node->class = $variable;
        return $node;
    }
    private function isAllowed(\PhpParser\Node\Expr $expr) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\Variable || $expr instanceof \PhpParser\Node\Expr\ArrayDimFetch || $expr instanceof \PhpParser\Node\Expr\PropertyFetch || $expr instanceof \PhpParser\Node\Expr\StaticPropertyFetch;
    }
    private function isAssign(\PhpParser\Node\Expr $expr) : bool
    {
        return $expr instanceof \PhpParser\Node\Expr\Assign || $expr instanceof \PhpParser\Node\Expr\AssignRef || $expr instanceof \PhpParser\Node\Expr\AssignOp;
    }
    private function isBetweenParentheses(\PhpParser\Node $node) : bool
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
}
