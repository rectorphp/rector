<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\ValueObject\OpeningAndClosingSpace;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\ManyPhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;

final class JoinTableTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface, ManyPhpAttributableTagNodeInterface
{
    /**
     * @var string
     */
    private const JOIN_COLUMNS = 'joinColumns';

    /**
     * @var string
     */
    private const INVERSE_JOIN_COLUMNS = 'inverseJoinColumns';

    /**
     * @var string
     */
    private $name;

    /**
     * @var JoinColumnTagValueNode[]
     */
    private $joinColumns = [];

    /**
     * @var JoinColumnTagValueNode[]
     */
    private $inverseJoinColumns = [];

    /**
     * @var OpeningAndClosingSpace
     */
    private $inverseJoinColumnsOpeningAndClosingSpace;

    /**
     * @var OpeningAndClosingSpace
     */
    private $joinColumnsOpeningAndClosingSpace;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @param JoinColumnTagValueNode[] $joinColumns
     * @param JoinColumnTagValueNode[] $inverseJoinColumns
     */
    public function __construct(
        string $name,
        ?string $schema = null,
        array $joinColumns = [],
        array $inverseJoinColumns = [],
        ?string $originalContent = null,
        OpeningAndClosingSpace $joinColumnsOpeningAndClosingSpace,
        OpeningAndClosingSpace $inverseJoinColumnsOpeningAndClosingSpace
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->joinColumns = $joinColumns;
        $this->inverseJoinColumns = $inverseJoinColumns;
        $this->resolveOriginalContentSpacingAndOrder($originalContent);
        $this->inverseJoinColumnsOpeningAndClosingSpace = $inverseJoinColumnsOpeningAndClosingSpace;
        $this->joinColumnsOpeningAndClosingSpace = $joinColumnsOpeningAndClosingSpace;
    }

    public function __toString(): string
    {
        $items = $this->createItems();
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\JoinTable';
    }

    /**
     * @return mixed[]
     */
    public function getAttributableItems(): array
    {
        $items = [];

        $items['name'] = $this->name;

        if ($this->schema !== null) {
            $items['schema'] = $this->schema;
        }

        return $items;
    }

    /**
     * @return array<string, mixed[]>
     */
    public function provide(): array
    {
        $items = [];

        foreach ($this->joinColumns as $joinColumn) {
            $items[$joinColumn->getShortName()] = $joinColumn->getAttributableItems();
        }

        foreach ($this->inverseJoinColumns as $inverseJoinColumn) {
            $items['@ORM\InverseJoinColumn'] = $inverseJoinColumn->getAttributableItems();
        }

        return $items;
    }

    public function getAttributeClassName(): string
    {
        return 'TBA';
    }

    /**
     * @return string[]
     */
    private function createItems(): array
    {
        $items = [];

        $items['name'] = sprintf('"%s"', $this->name);

        if ($this->schema !== null) {
            $items['schema'] = sprintf('"%s"', $this->schema);
        }

        $joinColumnItems = $this->createJoinColumnItems(self::JOIN_COLUMNS, self::INVERSE_JOIN_COLUMNS);
        return array_merge($items, $joinColumnItems);
    }

    /**
     * @return array<string, mixed>
     */
    private function createJoinColumnItems(string $joinColumnsKey, string $inverseJoinColumnsKey): array
    {
        $items = [];

        if ($this->joinColumns !== []) {
            $items[$joinColumnsKey] = $this->printNestedTag(
                $this->joinColumns,
                false,
                $this->joinColumnsOpeningAndClosingSpace->getOpeningSpace(),
                $this->joinColumnsOpeningAndClosingSpace->getClosingSpace()
            );
        }

        if ($this->inverseJoinColumns !== []) {
            $items[$inverseJoinColumnsKey] = $this->printNestedTag(
                $this->inverseJoinColumns,
                false,
                $this->inverseJoinColumnsOpeningAndClosingSpace->getOpeningSpace(),
                $this->inverseJoinColumnsOpeningAndClosingSpace->getClosingSpace()
            );
        }

        return $items;
    }
}
