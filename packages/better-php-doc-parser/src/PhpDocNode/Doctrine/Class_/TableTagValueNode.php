<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class TableTagValueNode extends AbstractDoctrineTagValueNode implements SilentKeyNodeInterface
{
    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $schema;

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
     * @var mixed[]
     */
    private $options = [];

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
        $this->name = $name;
        $this->schema = $schema;
        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;
        $this->options = $options;

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
        $items = $this->createItems();
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

    private function createItems(): array
    {
        $items = [];

        if ($this->name !== null) {
            $items['name'] = sprintf('"%s"', $this->name);
        }

        if ($this->schema !== null) {
            $items['schema'] = sprintf('"%s"', $this->schema);
        }

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

        if ($this->options !== []) {
            $items['options'] = $this->printArrayItem($this->options, 'options');
        }

        return $items;
    }
}
