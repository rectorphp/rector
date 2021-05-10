<?php

declare(strict_types=1);

namespace Rector\Renaming\ValueObject;

final class PseudoNamespaceToNamespace
{
    /**
     * @param string[] $excludedClasses
     */
    public function __construct(
        private string $namespacePrefix,
        private array $excludedClasses = []
    ) {
    }

    public function getNamespacePrefix(): string
    {
        return $this->namespacePrefix;
    }

    /**
     * @return string[]
     */
    public function getExcludedClasses(): array
    {
        return $this->excludedClasses;
    }
}
