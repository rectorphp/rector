<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class TableTagValueNode extends AbstractDoctrineTagValueNode implements SilentKeyNodeInterface
{
    /**
     * @var bool
     */
    private $haveIndexesFinalComma = false;

    /**
     * @var bool
     */
    private $haveUniqueConstraintsFinalComma = false;

    /**
     * @var string|null
     */
    private $indexesOpeningSpace;

    /**
     * @var string|null
     */
    private $indexesClosingSpace;

    /**
     * @var string|null
     */
    private $uniqueConstraintsOpeningSpace;

    /**
     * @var string|null
     */
    private $uniqueConstraintsClosingSpace;

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
        array $options,
        ?string $originalContent = null,
        bool $haveIndexesFinalComma = false,
        bool $haveUniqueConstraintsFinalComma = false,
        ?string $indexesOpeningSpace = null,
        ?string $indexesClosingSpace = null,
        ?string $uniqueConstraintsOpeningSpace = null,
        ?string $uniqueConstraintsClosingSpace = null
    ) {
        $this->items['name'] = $name;
        $this->items['schema'] = $schema;
        $this->items['options'] = $options;

        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);

        $this->haveIndexesFinalComma = $haveIndexesFinalComma;
        $this->haveUniqueConstraintsFinalComma = $haveUniqueConstraintsFinalComma;
        $this->indexesOpeningSpace = $indexesOpeningSpace;
        $this->indexesClosingSpace = $indexesClosingSpace;
        $this->uniqueConstraintsOpeningSpace = $uniqueConstraintsOpeningSpace;
        $this->uniqueConstraintsClosingSpace = $uniqueConstraintsClosingSpace;
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

    private function addCustomItems(array $items): array
    {
        if ($this->indexes !== []) {
            $items['indexes'] = $this->printNestedTag(
                $this->indexes,
                $this->haveIndexesFinalComma,
                $this->indexesOpeningSpace,
                $this->indexesClosingSpace
            );
        }

        if ($this->uniqueConstraints !== []) {
            $items['uniqueConstraints'] = $this->printNestedTag(
                $this->uniqueConstraints,
                $this->haveUniqueConstraintsFinalComma,
                $this->uniqueConstraintsOpeningSpace,
                $this->uniqueConstraintsClosingSpace
            );
        }

        return $items;
    }
}
