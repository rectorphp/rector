<?php

declare (strict_types=1);
namespace RectorPrefix20220606\Rector\Renaming\ValueObject;

final class PseudoNamespaceToNamespace
{
    /**
     * @readonly
     * @var string
     */
    private $namespacePrefix;
    /**
     * @var string[]
     * @readonly
     */
    private $excludedClasses = [];
    /**
     * @param string[] $excludedClasses
     */
    public function __construct(string $namespacePrefix, array $excludedClasses = [])
    {
        $this->namespacePrefix = $namespacePrefix;
        $this->excludedClasses = $excludedClasses;
    }
    public function getNamespacePrefix() : string
    {
        return $this->namespacePrefix;
    }
    /**
     * @return string[]
     */
    public function getExcludedClasses() : array
    {
        return $this->excludedClasses;
    }
}
