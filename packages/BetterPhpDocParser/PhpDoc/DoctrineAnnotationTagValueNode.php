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
     * @param array<mixed, mixed> $values
     */
    public function __construct(
        // values
        string $annotationClass,
        ?string $originalContent = null,
        array $values = [],
        ?string $silentKey = null
    ) {
        $this->hasChanged = true;
        $this->annotationClass = $annotationClass;

        parent::__construct($values, $originalContent, $silentKey);
    }

    public function __toString(): string
    {
        if (! $this->hasChanged) {
            if ($this->originalContent === null) {
                return '';
            }

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

        // without modifications, @todo split into items if needed
        return sprintf(
            '(%s%s%s)',
            $this->tagValueNodeConfiguration->hasNewlineBeforeClosing() ? PHP_EOL : '',
            $itemContents,
            $this->tagValueNodeConfiguration->hasNewlineAfterOpening() ? PHP_EOL : ''
        );
    }

    public function getAnnotationClass(): string
    {
        return $this->annotationClass;
    }
}
