<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Rector\Class_;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use Rector\Configuration\Deprecation\Contract\DeprecatedInterface;
use Rector\Exception\ShouldNotHappenException;
use Rector\Rector\AbstractRector;
use Symplify\RuleDocGenerator\ValueObject\CodeSample\CodeSample;
use Symplify\RuleDocGenerator\ValueObject\RuleDefinition;
/**
 * @deprecated as the removal has no effect on the mapping, while the missing attribute makes the entity harder to
 *             read. Keeping an explicit #[ORM\Table] is a valid style choice.
 */
final class RemoveEmptyTableAttributeRector extends AbstractRector implements DeprecatedInterface
{
    public function getRuleDefinition(): RuleDefinition
    {
        return new RuleDefinition("Remove empty Table attribute on entities because it's useless", [new CodeSample(<<<'CODE_SAMPLE'
<?php

use Doctrine\ORM\Mapping as ORM;
#[ORM\Table]
#[ORM\Entity]
class Product
{
}
CODE_SAMPLE
, <<<'CODE_SAMPLE'
<?php

use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
class Product
{
}
CODE_SAMPLE
)]);
    }
    /**
     * @return array<class-string<Node>>
     */
    public function getNodeTypes(): array
    {
        return [Class_::class];
    }
    /**
     * @param Class_ $node
     */
    public function refactor(Node $node): ?Node
    {
        throw new ShouldNotHappenException(sprintf('"%s" is deprecated and should not be used anymore. Remove it from your config files.', self::class));
    }
}
