<?php

declare (strict_types=1);
namespace Rector\Doctrine\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Core\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @see \Rector\Doctrine\Tests\Rector\Class_\RemoveEmptyTableAttributeRector\RemoveEmptyTableAttributeRectorTest
 */
final class RemoveEmptyTableAttributeRector extends AbstractRector
{
    public function getRuleDefinition() : RuleDefinition
    {
        return new RuleDefinition("Remove empty Table attribute on entities because it's useless", [new CodeSample(<<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202306;

use RectorPrefix202306\Doctrine\ORM\Mapping as ORM;
#[\Doctrine\ORM\Mapping\Table]
#[\Doctrine\ORM\Mapping\Entity]
class Product
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

namespace RectorPrefix202306;

use RectorPrefix202306\Doctrine\ORM\Mapping as ORM;
#[\Doctrine\ORM\Mapping\Entity]
class Product
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes() : array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node) : ?Node
    {
        $hasChanged = \false;
        foreach ($node->attrGroups as $attrGroupKey => $attrGroup) {
            foreach ($attrGroup->attrs as $key => $attribute) {
                if (!$this->nodeNameResolver->isName($attribute, 'Doctrine\\ORM\\Mapping\\Table')) {
                    continue;
                }
                if ($attribute->args !== []) {
                    continue;
                }
                unset($attrGroup->attrs[$key]);
                $hasChanged = \true;
            }
            if ($attrGroup->attrs === []) {
                unset($node->attrGroups[$attrGroupKey]);
            }
        }
        if ($hasChanged) {
            return $node;
        }
        return null;
    }
}
