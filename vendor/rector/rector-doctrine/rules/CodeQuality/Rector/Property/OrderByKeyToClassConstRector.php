<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Property;

use PhpParser\Node;
use PhpParser\Node\Attribute;
use PhpParser\Node\Expr\Array_;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Property;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\CodeQuality\Rector\Property\OrderByKeyToClassConstRector\OrderByKeyToClassConstRectorTest
 */
final class OrderByKeyToClassConstRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition('Replace OrderBy Attribute ASC/DESC with class constant from Criteria', [new CodeSample(<<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class ReplaceOrderByAscWithClassConstant
{
    #[ORM\OrderBy(['createdAt' => 'ASC'])]
    protected \DateTimeInterface $messages;
}
?>
CODE_SAMPLE
, <<<'CODE_SAMPLE'
use Doctrine\ORM\Mapping as ORM;

class ReplaceOrderByAscWithClassConstant
{
    #[ORM\OrderBy(['createdAt' => \Doctrine\Common\Collections\Criteria::ASC])]
    protected \DateTimeInterface $messages;
}
?>
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Property::class];
    }
    /**
     * @param Property $node
     */
    public function refactor(Node $node) : ?Node
    {
        $nodeAttribute = null;
        foreach ($node->attrGroups as $attrGroup) {
            foreach ($attrGroup->attrs as $attr) {
                if ($attr->name->toString() === 'Doctrine\\ORM\\Mapping\\OrderBy') {
                    $nodeAttribute = $attr;
                    break 2;
                }
            }
        }
        // If Attribute is not OrderBy, return null
        if (!$nodeAttribute instanceof Attribute) {
            return null;
        }
        if (!isset($nodeAttribute->args[0])) {
            return null;
        }
        if (!$nodeAttribute->args[0]->value instanceof Array_) {
            return null;
        }
        if (!isset($nodeAttribute->args[0]->value->items[0])) {
            return null;
        }
        if (!$nodeAttribute->args[0]->value->items[0]->value instanceof String_) {
            return null;
        }
        // If Attribute value from key is not `ASC` or `DESC`, return null
        if (!\in_array($nodeAttribute->args[0]->value->items[0]->value->value, ['ASC', 'asc', 'DESC', 'desc'], \true)) {
            return null;
        }
        $upper = \strtoupper($nodeAttribute->args[0]->value->items[0]->value->value);
        $nodeAttribute->args[0]->value->items[0]->value = $this->nodeFactory->createClassConstFetch('Doctrine\\Common\\Collections\\Criteria', $upper);
        return $node;
    }
}
