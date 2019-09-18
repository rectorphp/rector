<?php declare(strict_types=1);

namespace Rector\DoctrinePhpDocParser\Ast\PhpDoc\Class_;

use Rector\DoctrinePhpDocParser\Ast\PhpDoc\AbstractDoctrineTagValueNode;

abstract class AbstractIndexTagValueNode extends AbstractDoctrineTagValueNode
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var mixed[]|null
     */
    private $flags;

    /**
     * @var mixed[]|null
     */
    private $columns;

    /**
     * @var mixed[]|null
     */
    private $options;

    /**
     * @param mixed[]|null $columns
     * @param mixed[]|null $flags
     * @param mixed[]|null $options
     */
    public function __construct(
        string $name,
        ?array $columns,
        ?array $flags,
        ?array $options,
        ?string $originalContent = null
    ) {
        $this->name = $name;
        $this->flags = $flags;
        $this->options = $options;
        $this->columns = $columns;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->name !== null) {
            $contentItems['name'] = sprintf('name="%s"', $this->name);
        }

        if ($this->flags) {
            $contentItems['flags'] = $this->printArrayItem($this->flags, 'flags');
        }

        if ($this->options) {
            $contentItems['options'] = $this->printArrayItem($this->options, 'options');
        }

        if ($this->columns) {
            $contentItems['columns'] = $this->printArrayItem($this->columns, 'columns');
        }

        return $this->printContentItems($contentItems);
    }
}
