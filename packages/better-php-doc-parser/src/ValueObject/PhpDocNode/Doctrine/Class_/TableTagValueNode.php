<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\SilentKeyNodeInterface;
use Rector\BetterPhpDocParser\Printer\ArrayPartPhpDocTagPrinter;
use Rector\BetterPhpDocParser\Printer\TagValueNodePrinter;
use Rector\BetterPhpDocParser\ValueObject\AroundSpaces;
use Rector\BetterPhpDocParser\ValueObject\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\Core\Exception\ShouldNotHappenException;

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
     * @var AroundSpaces|null
     */
    private $indexesAroundSpaces;

    /**
     * @var AroundSpaces|null
     */
    private $uniqueConstraintsAroundSpaces;

    /**
     * @param mixed[] $options
     * @param IndexTagValueNode[] $indexes
     * @param UniqueConstraintTagValueNode[] $uniqueConstraints
     */
    public function __construct(
        ArrayPartPhpDocTagPrinter $arrayPartPhpDocTagPrinter,
        TagValueNodePrinter $tagValueNodePrinter,
        ?string $name,
        ?string $schema,
        array $indexes,
        array $uniqueConstraints,
        array $options,
        ?string $originalContent = null,
        bool $haveIndexesFinalComma = false,
        bool $haveUniqueConstraintsFinalComma = false,
        ?AroundSpaces $indexesAroundSpaces = null,
        ?AroundSpaces $uniqueConstraintsAroundSpaces = null
    ) {
        parent::__construct($arrayPartPhpDocTagPrinter, $tagValueNodePrinter, [], $originalContent);

        $this->items['name'] = $name;
        $this->items['schema'] = $schema;
        $this->items['options'] = $options;

        $this->indexes = $indexes;
        $this->uniqueConstraints = $uniqueConstraints;

        $this->haveIndexesFinalComma = $haveIndexesFinalComma;
        $this->haveUniqueConstraintsFinalComma = $haveUniqueConstraintsFinalComma;
        $this->indexesAroundSpaces = $indexesAroundSpaces;
        $this->uniqueConstraintsAroundSpaces = $uniqueConstraintsAroundSpaces;
    }

    public function __toString(): string
    {
        $items = $this->items;
        $items = $this->addCustomItems($items);

        $items = $this->tagValueNodePrinter->completeItemsQuotes(
            $this->tagValueNodeConfiguration,
            $items,
            ['indexes', 'uniqueConstraints']
        );
        $items = $this->filterOutMissingItems($items);
        $items = $this->tagValueNodePrinter->makeKeysExplicit($items, $this->tagValueNodeConfiguration);

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
            if (! $this->indexesAroundSpaces instanceof AroundSpaces) {
                throw new ShouldNotHappenException();
            }

            $items['indexes'] = $this->printNestedTag(
                $this->indexes,
                $this->haveIndexesFinalComma,
                $this->indexesAroundSpaces->getOpeningSpace(),
                $this->indexesAroundSpaces->getClosingSpace()
            );
        }

        if ($this->uniqueConstraints !== []) {
            if (! $this->uniqueConstraintsAroundSpaces instanceof AroundSpaces) {
                throw new ShouldNotHappenException();
            }

            $items['uniqueConstraints'] = $this->printNestedTag(
                $this->uniqueConstraints,
                $this->haveUniqueConstraintsFinalComma,
                $this->uniqueConstraintsAroundSpaces->getOpeningSpace(),
                $this->uniqueConstraintsAroundSpaces->getClosingSpace()
            );
        }

        return $items;
    }
}
