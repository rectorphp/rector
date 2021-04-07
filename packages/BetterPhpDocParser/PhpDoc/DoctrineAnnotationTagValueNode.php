<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\PhpDoc;

use Rector\BetterPhpDocParser\ValueObject\PhpDoc\DoctrineAnnotation\AbstractValuesAwareNode;

final class DoctrineAnnotationTagValueNode extends AbstractValuesAwareNode
{
    /**
     * @var string
     */
    private $annotationClass;

    /**
     * @var string|null
     */
    private $originalContent;

    /**
     * @param array<mixed, mixed> $values
     */
    public function __construct(
        // values
        string $annotationClass,
        ?string $originalContent = null,
        array $values = [],
        ?string $silentKey = null
    ) {
        $this->annotationClass = $annotationClass;
        $this->originalContent = $originalContent;

        parent::__construct($values, $silentKey);
    }

    public function __toString(): string
    {
        if (! $this->hasChanged && $this->originalContent !== null) {
            return $this->originalContent;
        }

        if ($this->values === []) {
            if ($this->originalContent === '()') {
                // empty brackets
                return $this->originalContent;
            }

            return '';
        }

        $itemContents = $this->printValuesContent($this->values);
        return sprintf('(%s)', $itemContents);
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }
}
