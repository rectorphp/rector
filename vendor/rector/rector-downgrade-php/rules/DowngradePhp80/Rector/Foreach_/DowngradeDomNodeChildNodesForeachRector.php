<?php

declare (strict_types=1);
namespace Rector\DowngradePhp80\Rector\Foreach_;

use PhpParser\Node;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Expr\BinaryOp\Coalesce;
use PhpParser\Node\Expr\PropertyFetch;
use PhpParser\Node\Stmt\Foreach_;
use PHPStan\Type\ObjectType;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @changelog https://github.com/php/php-src/pull/5180 https://bugs.php.net/bug.php?id=79271
 *
 * As of PHP 8.0 DOMNode::$childNodes always returns a DOMNodeList. On older
 * versions it returns null for nodes that cannot have children (e.g. DOMText),
 * which makes a foreach over it emit "Invalid argument supplied for foreach()".
 *
 * @see \Rector\Tests\DowngradePhp80\Rector\Foreach_\DowngradeDomNodeChildNodesForeachRector\DowngradeDomNodeChildNodesForeachRectorTest
 */
final class DowngradeDomNodeChildNodesForeachRector extends AbstractRector
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition('Add null coalesce to a foreach over DOMNode::$childNodes, as it can be null before PHP 8.0', [new CodeSample(<<<'CODE_SAMPLE'
function run(\DOMNode $node)
{
    foreach ($node->childNodes as $childNode) {
        echo $childNode->nodeValue;
    }
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
function run(\DOMNode $node)
{
    foreach ($node->childNodes ?? [] as $childNode) {
        echo $childNode->nodeValue;
    }
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Foreach_::class];
    }
    /**
     * @param Foreach_ $node
     */
    public function refactor(Node $node): ?Node
    {
        $iteratedExpr = $node->expr;
        if (!$iteratedExpr instanceof PropertyFetch) {
            return null;
        }
        if (!$this->isName($iteratedExpr->name, 'childNodes')) {
            return null;
        }
        $callerType = $this->nodeTypeResolver->getType($iteratedExpr->var);
        if (!$callerType instanceof ObjectType) {
            return null;
        }
        if (!$callerType->isInstanceOf('DOMNode')->yes()) {
            return null;
        }
        $node->expr = new Coalesce($iteratedExpr, new Array_([]));
        return $node;
    }
}
