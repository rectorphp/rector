<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class JoinColumnTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface, PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var bool|null
     */
    private $nullable;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $referencedColumnName;

    /**
     * @var bool|null
     */
    private $unique;

    /**
     * @var string|null
     */
    private $onDelete;

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @var string|null
     */
    private $fieldName;

    /**
     * @var string|null
     */
    private $tag;

    /**
     * @var string
     */
    private $shortName = '@ORM\JoinColumn';

    public function __construct(
        ?string $name,
        ?string $referencedColumnName,
        ?bool $unique = null,
        ?bool $nullable = null,
        ?string $onDelete = null,
        ?string $columnDefinition = null,
        ?string $fieldName = null,
        ?string $originalContent = null,
        ?string $originalTag = null
    ) {
        $this->nullable = $nullable;
        $this->name = $name;
        $this->referencedColumnName = $referencedColumnName;
        $this->unique = $unique;
        $this->onDelete = $onDelete;
        $this->columnDefinition = $columnDefinition;
        $this->fieldName = $fieldName;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
            $this->tag = $originalTag;
        }
    }

    public function __toString(): string
    {
        $items = $this->createItems();
        $items = $this->makeKeysExplicit($items);

        return $this->printContentItems($items);
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function getTag(): ?string
    {
        return $this->tag ?: $this->shortName;
    }

    public function getUnique(): ?bool
    {
        return $this->unique;
    }

    public function getShortName(): string
    {
        return $this->shortName;
    }

    public function changeShortName(string $shortName): void
    {
        $this->shortName = $shortName;
    }

    public function toAttributeString(): string
    {
        $items = $this->createItems();
        $items = $this->filterOutMissingItems($items);

        // specific for attributes
        foreach ($items as $key => $value) {
            if ($key !== 'unique') {
                continue;
            }
            if ($value !== 'true') {
                continue;
            }
            $items[$key] = 'ORM\JoinColumn::UNIQUE';
        }

        $content = $this->printPhpAttributeItems($items);

        return $this->printAttributeContent($content);
    }

    private function createItems(): array
    {
        $items = [];

        if ($this->name) {
            $items['name'] = sprintf('"%s"', $this->name);
        }

        if ($this->referencedColumnName !== null) {
            $items['referencedColumnName'] = sprintf('"%s"', $this->referencedColumnName);
        }

        if ($this->nullable !== null) {
            $items['nullable'] = $this->nullable ? 'true' : 'false';
        }

        // skip default value
        if ($this->unique !== null) {
            $items['unique'] = $this->unique ? 'true' : 'false';
        }

        if ($this->onDelete !== null) {
            $items['onDelete'] = sprintf('"%s"', $this->onDelete);
        }

        if ($this->columnDefinition !== null) {
            $items['columnDefinition'] = sprintf('"%s"', $this->columnDefinition);
        }

        if ($this->fieldName !== null) {
            $items['fieldName'] = sprintf('"%s"', $this->fieldName);
        }
        return $items;
    }
}
