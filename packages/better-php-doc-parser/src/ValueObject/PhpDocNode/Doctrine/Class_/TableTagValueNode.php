<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\OpeningAndClosingSpace;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

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
     * @var IndexTagValueNode[]
     */
    private $indexes = [];

    /**
     * @var UniqueConstraintTagValueNode[]
     */
    private $uniqueConstraints = [];

    /**
     * @var OpeningAndClosingSpace
     */
    private $indexesOpeningAndClosingSpace;

    /**
     * @var OpeningAndClosingSpace
     */
    private $uniqueConstraintsOpeningAndClosingSpace;

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
        OpeningAndClosingSpace $indexesOpeningAndClosingSpace,
        OpeningAndClosingSpace $uniqueConstraintsOpeningAndClosingSpace
    ) {
        $this->items['name'] = $name;
        $this->items['schema'] = $schema;
        $this->items['options'] = $options;

        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;

        $this->resolveOriginalContentSpacingAndOrder($originalContent);

        $this->haveIndexesFinalComma = $haveIndexesFinalComma;
        $this->haveUniqueConstraintsFinalComma = $haveUniqueConstraintsFinalComma;
        $this->indexesOpeningAndClosingSpace = $indexesOpeningAndClosingSpace;
        $this->uniqueConstraintsOpeningAndClosingSpace = $uniqueConstraintsOpeningAndClosingSpace;
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
            $items['indexes'] = $this->printNestedTag(
                $this->indexes,
                $this->haveIndexesFinalComma,
                $this->indexesOpeningAndClosingSpace->getOpeningSpace(),
                $this->indexesOpeningAndClosingSpace->getClosingSpace()
            );
        }

        if ($this->uniqueConstraints !== []) {
            $items['uniqueConstraints'] = $this->printNestedTag(
                $this->uniqueConstraints,
                $this->haveUniqueConstraintsFinalComma,
                $this->uniqueConstraintsOpeningAndClosingSpace->getOpeningSpace(),
                $this->uniqueConstraintsOpeningAndClosingSpace->getClosingSpace()
            );
        }

        return $items;
    }
}
