<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\DowngradePhp80\Rector\New_;

use RectorPrefix20220606\PhpParser\Node;
use RectorPrefix20220606\PhpParser\Node\Expr;
use RectorPrefix20220606\PhpParser\Node\Expr\ArrayDimFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Assign;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignOp;
use RectorPrefix20220606\PhpParser\Node\Expr\AssignRef;
use RectorPrefix20220606\PhpParser\Node\Expr\Instanceof_;
use RectorPrefix20220606\PhpParser\Node\Expr\New_;
use RectorPrefix20220606\PhpParser\Node\Expr\PropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\StaticPropertyFetch;
use RectorPrefix20220606\PhpParser\Node\Expr\Variable;
use RectorPrefix20220606\Rector\Core\PhpParser\Node\NamedVariableFactory;
use RectorPrefix20220606\Rector\Core\Rector\AbstractRector;
use RectorPrefix20220606\Rector\NodeTypeResolver\Node\AttributeKey;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use RectorPrefix20220606\Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://wiki.php.net/rfc/variable_syntax_tweaks#arbitrary_expression_support_for_new_and_instanceof
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\New_\DowngradeArbitraryExpressionsSupportRector\DowngradeArbitraryExpressionsSupportRectorTest
 */
final class DowngradeArbitraryExpressionsSupportRector extends AbstractRector
{
    /**
     * @readonly
     * @var \Rector\Core\PhpParser\Node\NamedVariableFactory
     */
    private $namedVariableFactory;
    public function __construct(NamedVariableFactory $namedVariableFactory)
    {
        $this->namedVariableFactory = $namedVariableFactory;
    }
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace arbitrary expressions used with new or instanceof.', [new CodeSample(<<<'CODE_SAMPLE'
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
        return [Instanceof_::class, New_::class];
    }
    /**
     * @param Instanceof_|New_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        if (!$node->class instanceof Expr) {
            return null;
        }
        $isAllowed = $this->isAllowed($node->class);
        $toSkip = $isAllowed && $this->isBetweenParentheses($node);
        if ($toSkip) {
            return null;
        }
        // mandatory to remove parentheses
        $node->setAttribute(AttributeKey::ORIGINAL_NODE, null);
        if ($isAllowed) {
            return $node;
        }
        if ($this->isAssign($node->class)) {
            /** @var Assign|AssignRef|AssignOp $assign */
            $assign = $node->class;
            $variable = $assign->var;
        } else {
            $variable = $this->namedVariableFactory->createVariable($node, 'className');
            $assign = new Assign($variable, $node->class);
        }
        $this->nodesToAddCollector->addNodeBeforeNode($assign, $node, $this->file->getSmartFileInfo());
        $node->class = $variable;
        return $node;
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
}
