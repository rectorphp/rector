<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class TableTagValueNode extends AbstractDoctrineTagValueNode
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

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }

        $this->haveIndexesFinalComma = $haveIndexesFinalComma;
        $this->haveUniqueConstraintsFinalComma = $haveUniqueConstraintsFinalComma;
        $this->indexesOpeningSpace = $indexesOpeningSpace;
        $this->indexesClosingSpace = $indexesClosingSpace;
        $this->uniqueConstraintsOpeningSpace = $uniqueConstraintsOpeningSpace;
        $this->uniqueConstraintsClosingSpace = $uniqueConstraintsClosingSpace;
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->name !== null) {
            if ($this->originalContent !== null && ! in_array('name', (array) $this->orderedVisibleItems, true)) {
                $contentItems[] = '"' . $this->name . '"';
            } else {
                $contentItems['name'] = sprintf('name="%s"', $this->name);
            }
        }

        if ($this->schema !== null) {
            $contentItems['schema'] = sprintf('schema="%s"', $this->schema);
        }

        if ($this->indexes !== []) {
            $contentItems['indexes'] = $this->printNestedTag(
                $this->indexes,
                'indexes',
                $this->haveIndexesFinalComma,
                $this->indexesOpeningSpace,
                $this->indexesClosingSpace
            );
        }

        if ($this->uniqueConstraints !== []) {
            $contentItems['uniqueConstraints'] = $this->printNestedTag(
                $this->uniqueConstraints,
                'uniqueConstraints',
                $this->haveUniqueConstraintsFinalComma,
                $this->uniqueConstraintsOpeningSpace,
                $this->uniqueConstraintsClosingSpace
            );
        }

        if ($this->options !== []) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@ORM\Table';
    }
}
