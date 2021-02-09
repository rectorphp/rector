<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\AroundSpaces;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\PhpAttribute\Contract\ManyPhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\Printer\PhpAttributeGroupFactory;

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
     * @var string|null
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
     * @var AroundSpaces|null
     */
    private $inverseJoinColumnsAroundSpaces;

    /**
     * @var AroundSpaces|null
     */
    private $joinColumnsAroundSpaces;

    /**
     * @var string|null
     */
    private $schema;

    /**
     * @param JoinColumnTagValueNode[] $joinColumns
     * @param JoinColumnTagValueNode[] $inverseJoinColumns
     */
    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        ?string $name,
        ?string $schema = null,
        array $joinColumns = [],
        array $inverseJoinColumns = [],
        ?string $originalContent = null,
        ?AroundSpaces $joinColumnsAroundSpaces = null,
        ?AroundSpaces $inverseJoinColumnsAroundSpaces = null
    ) {
        $this->name = $name;
        $this->schema = $schema;
        $this->joinColumns = $joinColumns;
        $this->inverseJoinColumns = $inverseJoinColumns;

        // $this->resolveOriginalContentSpacingAndOrder($originalContent);

        $this->inverseJoinColumnsAroundSpaces = $inverseJoinColumnsAroundSpaces;
        $this->joinColumnsAroundSpaces = $joinColumnsAroundSpaces;

        parent::__construct($arrayPartPhpDocTagPrinter, $tagValueNodePrinter, [], $originalContent);
    }

    public function __toString(): string
    {
        $items = $this->createItems();
        $items = $this->tagValueNodePrinter->makeKeysExplicit($items, $this->tagValueNodeConfiguration);

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

        if ($this->name !== null) {
            $items['name'] = $this->name;
        }

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
        return PhpAttributeGroupFactory::TO_BE_ANNOUNCED;
    }

    /**
     * @return string[]
     */
    private function createItems(): array
    {
        $items = [];

        if ($this->name !== null) {
            $items['name'] = sprintf('"%s"', $this->name);
        }

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
            if (! $this->joinColumnsAroundSpaces instanceof AroundSpaces) {
                throw new ShouldNotHappenException();
            }

            $items[$joinColumnsKey] = $this->printNestedTag(
                $this->joinColumns,
                false,
                $this->joinColumnsAroundSpaces->getOpeningSpace(),
                $this->joinColumnsAroundSpaces->getClosingSpace()
            );
        }

        if ($this->inverseJoinColumns !== []) {
            if (! $this->inverseJoinColumnsAroundSpaces instanceof AroundSpaces) {
                throw new ShouldNotHappenException();
            }

            $items[$inverseJoinColumnsKey] = $this->printNestedTag(
                $this->inverseJoinColumns,
                false,
                $this->inverseJoinColumnsAroundSpaces->getOpeningSpace(),
                $this->inverseJoinColumnsAroundSpaces->getClosingSpace()
            );
        }

        return $items;
    }
}
