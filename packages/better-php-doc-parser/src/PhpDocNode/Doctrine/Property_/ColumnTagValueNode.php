<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Property_;

use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;
use Rector\PhpAttribute\Contract\PhpAttributableTagNodeInterface;
use Rector\PhpAttribute\PhpDocNode\PhpAttributePhpDocNodePrintTrait;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode implements PhpAttributableTagNodeInterface
{
    use PhpAttributePhpDocNodePrintTrait;

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var string|null
     */
    private $type;

    /**
     * @var mixed|null
     */
    private $length;

    /**
     * @var int|null
     */
    private $precision;

    /**
     * @var int|null
     */
    private $scale;

    /**
     * @var bool|null
     */
    private $unique;

    /**
     * @var bool|null
     */
    private $nullable;

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @var mixed[]|null
     */
    private $options;

    /**
     * @param mixed[] $options
     * @param mixed|null $length
     */
    public function __construct(
        ?string $name,
        ?string $type,
        $length,
        ?int $precision = null,
        ?int $scale = null,
        ?bool $unique = null,
        ?bool $nullable = null,
        ?array $options = null,
        ?string $columnDefinition = null,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->type = $type;
        $this->length = $length;
        $this->precision = $precision;
        $this->scale = $scale;
        $this->unique = $unique;
        $this->nullable = $nullable;
        $this->options = $options;
        $this->columnDefinition = $columnDefinition;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }
    }

    public function __toString(): string
    {
        $contentItems = $this->createItems();
        $contentItems = $this->makeKeysExplicit($contentItems);

        return $this->printContentItems($contentItems);
    }

    public function changeType(string $type): void
    {
        $this->type = $type;
    }

    public function getType(): ?string
    {
        return $this->type;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }

    public function getShortName(): string
    {
        return '@ORM\Column';
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
            $items[$key] = 'ORM\Column::UNIQUE';
        }

        $content = $this->printPhpAttributeItems($items);
        return $this->printAttributeContent($content);
    }

    /**
     * @return string[]
     */
    private function createItems(): array
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = sprintf('"%s"', $this->type);
        }

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('"%s"', $this->name);
        }

        if ($this->length !== null) {
            $contentItems['length'] = (string) $this->length;
        }

        if ($this->precision !== null) {
            $contentItems['precision'] = (string) $this->precision;
        }

        if ($this->scale !== null) {
            $contentItems['scale'] = (string) $this->scale;
        }

        if ($this->columnDefinition !== null) {
            $contentItems['columnDefinition'] = sprintf('"%s"', $this->columnDefinition);
        }

        if ($this->options) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->unique !== null) {
            $contentItems['unique'] = $this->unique ? 'true' : 'false';
        }

        if ($this->nullable !== null) {
            $contentItems['nullable'] = $this->nullable ? 'true' : 'false';
        }

        return $contentItems;
    }
}
