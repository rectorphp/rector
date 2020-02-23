<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

final class JoinTableTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @var string|null
     */
    private $joinColumnsOpeningSpace;

    /**
     * @var string|null
     */
    private $inverseJoinColumnsOpeningSpace;

    /**
     * @var string|null
     */
    private $joinColumnsClosingSpace;

    /**
     * @var string|null
     */
    private $inverseJoinColumnsClosingSpace;

    /**
     * @var JoinColumnTagValueNode[]|null
     */
    private $joinColumns;

    /**
     * @var JoinColumnTagValueNode[]|null
     */
    private $inverseJoinColumns;

    /**
     * @param JoinColumnTagValueNode[] $joinColumns
     * @param JoinColumnTagValueNode[] $inverseJoinColumns
     */
    public function __construct(
        string $name,
        ?string $schema = null,
        ?array $joinColumns = null,
        ?array $inverseJoinColumns = null,
        ?string $originalContent = null,
        ?string $joinColumnsOpeningSpace = null,
        ?string $joinColumnsClosingSpace = null,
        ?string $inverseJoinColumnsOpeningSpace = null,
        ?string $inverseJoinColumnsClosingSpace = null
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->joinColumns = $joinColumns;
        $this->inverseJoinColumns = $inverseJoinColumns;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
        $this->joinColumnsOpeningSpace = $joinColumnsOpeningSpace;
        $this->joinColumnsClosingSpace = $joinColumnsClosingSpace;
        $this->inverseJoinColumnsOpeningSpace = $inverseJoinColumnsOpeningSpace;
        $this->inverseJoinColumnsClosingSpace = $inverseJoinColumnsClosingSpace;
    }

    public function __toString(): string
    {
        $contentItems = [];

        $contentItems['name'] = sprintf('name="%s"', $this->name);

        if ($this->schema !== null) {
            $contentItems['schema'] = sprintf('schema="%s"', $this->schema);
        }

        if ($this->joinColumns) {
            $contentItems['joinColumns'] = $this->printNestedTag(
                $this->joinColumns,
                'joinColumns',
                false,
                $this->joinColumnsOpeningSpace,
                $this->joinColumnsClosingSpace
            );
        }

        if ($this->inverseJoinColumns) {
            $contentItems['inverseJoinColumns'] = $this->printNestedTag(
                $this->inverseJoinColumns,
                'inverseJoinColumns',
                false,
                $this->inverseJoinColumnsOpeningSpace,
                $this->inverseJoinColumnsClosingSpace
            );
        }

        return $this->printContentItems($contentItems);
    }

    public function getShortName(): string
    {
        return '@ORM\JoinTable';
    }
}
