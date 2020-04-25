<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDocNode\Gedmo;

use Rector\BetterPhpDocParser\Contract\PhpDocNode\ShortNameAwareTagInterface;
use Rector\BetterPhpDocParser\PhpDocNode\AbstractTagValueNode;

final class SlugTagValueNode extends AbstractTagValueNode implements ShortNameAwareTagInterface
{
    /**
     * @var mixed[]
     */
    private $fields = [];

    /**
     * @var bool
     */
    private $updatable = false;

    /**
     * @var string
     */
    private $style;

    /**
     * @var bool
     */
    private $unique = false;

    /**
     * @var string|null
     */
    private $uniqueBase;

    /**
     * @var string
     */
    private $separator;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $suffix;

    /**
     * @var mixed[]
     */
    private $handlers = [];

    /**
     * @var string|null
     */
    private $dateFormat;

    public function __construct(
        array $fields,
        bool $updatable,
        string $style,
        bool $unique,
        ?string $uniqueBase,
        string $separator,
        string $prefix,
        string $suffix,
        array $handlers,
        ?string $dateFormat,
        ?string $originalContent = null
    ) {
        $this->fields = $fields;
        $this->updatable = $updatable;
        $this->style = $style;
        $this->unique = $unique;
        $this->uniqueBase = $uniqueBase;
        $this->separator = $separator;
        $this->prefix = $prefix;
        $this->suffix = $suffix;
        $this->handlers = $handlers;

        $this->dateFormat = $dateFormat;

        if ($originalContent !== null) {
            $this->resolveOriginalContentSpacingAndOrder($originalContent, 'fields');
        }
    }

    public function __toString(): string
    {
        $contentItems['fields'] = $this->printValueWithOptionalQuotes('fields', $this->fields);

        if ($this->updatable) {
            $contentItems['updatable'] = sprintf('updatable=%s', $this->updatable ? 'true' : 'false');
        }

        if ($this->style !== '') {
            $contentItems['style'] = sprintf('style="%s"', $this->style);
        }

        if ($this->unique) {
            $contentItems['unique'] = sprintf('unique=%s', $this->unique ? 'true' : 'false');
        }

        if ($this->uniqueBase) {
            $contentItems['unique_base'] = sprintf('unique_base="%s"', $this->uniqueBase);
        }

        if ($this->separator !== '') {
            $contentItems['separator'] = sprintf('separator="%s"', $this->separator);
        }

        if ($this->prefix !== '') {
            $contentItems['prefix'] = sprintf('prefix="%s"', $this->prefix);
        }

        if ($this->suffix !== '') {
            $contentItems['suffix'] = sprintf('suffix="%s"', $this->suffix);
        }

        if ($this->dateFormat) {
            $contentItems['dateFormat'] = sprintf('dateFormat="%s"', $this->dateFormat);
        }

        if ($this->handlers !== []) {
            $contentItems['handlers'] = $this->printArrayItem($this->handlers, 'handlers');
        }

        return $this->printContentItems($contentItems);
    }

    public function getFields(): array
    {
        return $this->fields;
    }

    public function getShortName(): string
    {
        return '@Gedmo\Slug';
    }
}
