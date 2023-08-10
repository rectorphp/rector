<?php

declare (strict_types=1);
namespace Rector\Renaming\ValueObject;

/**
 * @api deprecated, soon to be removed
 */
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
