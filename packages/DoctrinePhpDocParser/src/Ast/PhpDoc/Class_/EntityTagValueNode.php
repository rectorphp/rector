<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class EntityTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string|null
     */
    private $repositoryClass;

    /**
     * @var bool
     */
    private $readOnly = false;

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
        $contentItems['readOnly'] = sprintf('readOnly=%s', $this->readOnly ? 'true' : 'false');

        return $this->printContentItems($contentItems);
    }

    public function removeRepositoryClass(): void
    {
        $itemPosition = array_search('repositoryClass', $this->orderedVisibleItems, true);
        if ($itemPosition !== null) {
            unset($this->orderedVisibleItems[$itemPosition]);
        }

        $this->repositoryClass = null;
    }
}
