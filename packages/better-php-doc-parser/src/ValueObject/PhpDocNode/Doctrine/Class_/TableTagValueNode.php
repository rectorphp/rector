<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

/**
 * @see \Rector\BetterPhpDocParser\PhpDocNodeFactory\Doctrine\Class_\TablePhpDocNodeFactory
 */
final class TableTagValueNode extends AbstractDoctrineTagValueNode implements SilentKeyNodeInterface
{
    /**
     * @var IndexTagValueNode[]
     */
    private $indexes = [];

    /**
     * @var UniqueConstraintTagValueNode[]
     */
    private $uniqueConstraints = [];

    /**
     * @param mixed[] $options
     * @param IndexTagValueNode[] $indexes
     * @param UniqueConstraintTagValueNode[] $uniqueConstraints
     */
    public function __construct(
        ?string $name,
        ?string $schema,
        array $indexes,
        array $uniqueConstraints,
        array $options
    ) {
        $this->items['name'] = $name;
        $this->items['schema'] = $schema;
        $this->items['options'] = $options;

        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;
    }

    public function __toString(): string
    {
        $items = $this->items;
        $items = $this->addCustomItems($items);

        $items = $this->completeItemsQuotes($items, ['indexes', 'uniqueConstraints']);
        $items = $this->filterOutMissingItems($items);
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\Table';
    }

    public function getSilentKey(): string
    {
        return 'name';
    }

    /**
     * @param mixed[] $items
     * @return mixed[]
     */
    private function addCustomItems(array $items): array
    {
        if ($this->indexes !== []) {
            $items['indexes'] = $this->printNestedTag($this->indexes);
        }

        if ($this->uniqueConstraints !== []) {
            $items['uniqueConstraints'] = $this->printNestedTag($this->uniqueConstraints);
        }

        return $items;
    }
}
