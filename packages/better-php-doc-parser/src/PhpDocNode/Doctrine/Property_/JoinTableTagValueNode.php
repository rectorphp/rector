<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class JoinTableTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

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
     * @var JoinColumnTagValueNode[]
     */
    private $joinColumns = [];

    /**
     * @var JoinColumnTagValueNode[]
     */
    private $inverseJoinColumns = [];

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
        $items = $this->createItems();
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function getShortName(): string
    {
        return '@ORM\JoinTable';
    }

    public function toAttributeString(): string
    {
        $items = $this->createItems();
        unset($items[self::JOIN_COLUMNS], $items[self::INVERSE_JOIN_COLUMNS]);

        $content = $this->printPhpAttributeItems($items);

        $joinTableAttributeContent = $this->printPhpAttributeContent($content);

        foreach ($this->joinColumns as $joinColumn) {
            $joinTableAttributeContent .= PHP_EOL . $joinColumn->toAttributeString();
        }

        foreach ($this->inverseJoinColumns as $inverseJoinColumnAttributeContent) {
            $inverseJoinColumnAttributeContent->changeShortName('ORM\InverseJoinColumn');
            $joinTableAttributeContent .= PHP_EOL . $inverseJoinColumnAttributeContent->toAttributeString();
        }

        return $joinTableAttributeContent;
    }

    private function createItems(): array
    {
        $items = [];

        $items['name'] = sprintf('"%s"', $this->name);

        if ($this->schema !== null) {
            $items['schema'] = sprintf('"%s"', $this->schema);
        }

        if ($this->joinColumns !== []) {
            $items[self::JOIN_COLUMNS] = $this->printNestedTag(
                $this->joinColumns,
                false,
                $this->joinColumnsOpeningSpace,
                $this->joinColumnsClosingSpace
            );
        }

        if ($this->inverseJoinColumns !== []) {
            $items[self::INVERSE_JOIN_COLUMNS] = $this->printNestedTag(
                $this->inverseJoinColumns,
                false,
                $this->inverseJoinColumnsOpeningSpace,
                $this->inverseJoinColumnsClosingSpace
            );
        }

        return $items;
    }
}
