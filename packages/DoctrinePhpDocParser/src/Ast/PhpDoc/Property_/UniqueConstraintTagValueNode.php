<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Property_;

use Rector\DoctrinePhpDocParser\Array_\ArrayItemStaticHelper;
use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

final class UniqueConstraintTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    public const SHORT_NAME = '@ORM\UniqueConstraint';

    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed[]
     */
    private $flags = [];

    /**
     * @var null|mixed[]
     */
    private $options;

    /**
     * @var mixed[]
     */
    private $columns = [];

    /**
     * @param mixed[] $columns
     * @param mixed[] $flags
     * @param mixed[] $options
     */
    public function __construct(
        string $name,
        array $columns,
        array $flags,
        ?array $options,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->flags = $flags;
        $this->options = $options;
        $this->columns = $columns;

        if ($originalContent !== null) {
            $this->orderedVisibleItems = ArrayItemStaticHelper::resolveAnnotationItemsOrder($originalContent);
        }
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->flags !== []) {
            $contentItems['flags'] = $this->printArrayItem($this->flags, 'flags');
        }

        if ($this->options) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->columns !== []) {
            $contentItems['columns'] = $this->printArrayItem($this->columns, 'columns');
        }

        return $this->printContentItems($contentItems);
    }
}
