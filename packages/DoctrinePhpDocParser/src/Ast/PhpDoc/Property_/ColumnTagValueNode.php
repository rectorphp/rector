<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class ColumnTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\Column';

    /**
     * @var string|null
     */
    private $name;

    /**
     * @var mixed
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
     * @var mixed[]|null
     */
    private $options;

    /**
     * @var string|null
     */
    private $columnDefinition;

    /**
     * @param mixed[] $options
     * @param mixed $type
     * @param mixed|null $length
     */
    public function __construct(
        ?string $name,
        $type,
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
            $this->orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent);
        }
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->type !== null) {
            $contentItems['type'] = sprintf('type="%s"', $this->type);
        }

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->length !== null) {
            $contentItems['length'] = sprintf('length=%s', $this->length);
        }

        if ($this->precision !== null) {
            $contentItems['precision'] = sprintf('precision=%s', $this->precision);
        }

        if ($this->scale !== null) {
            $contentItems['scale'] = sprintf('scale=%s', $this->scale);
        }

        if ($this->unique !== null) {
            $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        }

        if ($this->nullable !== null) {
            $contentItems['nullable'] = sprintf('nullable=%s', $this->nullable ? 'true' : 'false');
        }

        if ($this->options) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->columnDefinition !== null) {
            $contentItems['columnDefinition'] = sprintf('columnDefinition="%s"', $this->columnDefinition);
        }

        return $this->printContentItems($contentItems);
    }

    /**
     * @return mixed|null
     */
    public function getType()
    {
        return $this->type;
    }

    public function isNullable(): ?bool
    {
        return $this->nullable;
    }
}
