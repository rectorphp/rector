<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Doctrine\Class_;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\TagAwareNodeInterface;
use Rector\BetterPhpDocParser\PhpDocNode\Doctrine\AbstractDoctrineTagValueNode;

abstract class AbstractIndexTagValueNode extends AbstractDoctrineTagValueNode implements TagAwareNodeInterface
{
    /**
     * @var string|null
     */
    protected $tag;

    /**
     * @var string|null
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
        ?string $name,
        ?array $columns,
        ?array $flags,
        ?array $options,
        ?string $originalContent = null,
        ?string $originalTag = null
    ) {
        $this->name = $name;
        $this->flags = $flags;
        $this->options = $options;
        $this->columns = $columns;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent);
        }
        $this->tag = $originalTag;
    }

    public function __toString(): string
    {
        $contentItems = [];

        if ($this->name) {
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
