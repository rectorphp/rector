<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc;

use PHPStan\PhpDocParser\Ast\PhpDoc\PhpDocTagValueNode;
use Rector\BetterPhpDocParser\Attributes\Attribute\AttributeTrait;
use Rector\BetterPhpDocParser\Attributes\Contract\Ast\AttributeAwareNodeInterface;
use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Contract\Ast\PhpDoc\DoctrineTagNodeInterface;

final class EntityTagValueNode implements PhpDocTagValueNode, AttributeAwareNodeInterface, DoctrineTagNodeInterface
{
    use AttributeTrait;

    /**
     * @var string|null
     */
    private $repositoryClass;

    /**
     * @var bool
     */
    private $readOnly = false;

    /**
     * @var string[]
     */
    private $orderedVisibleItems = [];

    /**
     * @param string[] $orderedVisibleItems
     */
    public function __construct(?string $repositoryClass, bool $readOnly, array $orderedVisibleItems)
    {
        $this->repositoryClass = $repositoryClass;
        $this->readOnly = $readOnly;
        $this->orderedVisibleItems = $orderedVisibleItems;
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['repositoryClass'] = sprintf('repositoryClass="%s"', $this->repositoryClass);

        // default value
        $contentItems['readOnly'] = sprintf('readOnly=%s', $this->readOnly ? 'true' : 'false');

        $contentItems = ArrayItemStaticHelper::filterAndSortVisibleItems($contentItems, $this->orderedVisibleItems);
        if ($contentItems === []) {
            return '';
        }

        return '(' . implode(', ', $contentItems) . ')';
    }
}
