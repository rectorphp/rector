<?php

declare (strict_types=1);
namespace Rector\CodingStyle\ClassNameImport\ValueObject;

use PHPStan\Type\Type;
use Rector\StaticTypeMapper\ValueObject\Type\FullyQualifiedObjectType;
/**
 * Imports queued to be added on the next UseAddingPostRector run; scoped to a single file.
 * Unlike UsedImports, this is mutable and collected during the post-rector chain.
 */
final class PendingImports
{
    /**
     * @var FullyQualifiedObjectType[]
     */
    private array $useImports = [];
    /**
     * @var FullyQualifiedObjectType[]
     */
    private array $functionImports = [];
    /**
     * @var FullyQualifiedObjectType[]
     */
    private array $constantImports = [];
    public function addUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->useImports[] = $fullyQualifiedObjectType;
    }
    public function addFunctionUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->functionImports[] = $fullyQualifiedObjectType;
    }
    public function addConstantUseImport(FullyQualifiedObjectType $fullyQualifiedObjectType): void
    {
        $this->constantImports[] = $fullyQualifiedObjectType;
    }
    public function hasPendingUseImports(): bool
    {
        if ($this->useImports !== []) {
            return \true;
        }
        if ($this->functionImports !== []) {
            return \true;
        }
        return $this->constantImports !== [];
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getUseImports(): array
    {
        return $this->useImports;
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getFunctionImports(): array
    {
        return $this->functionImports;
    }
    /**
     * @return FullyQualifiedObjectType[]
     */
    public function getConstantImports(): array
    {
        return $this->constantImports;
    }
    public function isShortImported(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        $shortName = $fullyQualifiedObjectType->getShortName();
        foreach ($this->constantImports as $constantImport) {
            // don't compare strtolower for use const as insensitive is allowed, see https://3v4l.org/lteVa
            if ($constantImport->getShortName() === $shortName) {
                return \true;
            }
        }
        $shortName = strtolower($shortName);
        foreach ($this->useImports as $useImport) {
            if (strtolower($useImport->getShortName()) === $shortName) {
                return \true;
            }
        }
        $found = \false;
        foreach ($this->functionImports as $fullyQualifiedObjectType) {
            if (strtolower($fullyQualifiedObjectType->getShortName()) === $shortName) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
    public function isImportShortable(FullyQualifiedObjectType $fullyQualifiedObjectType): bool
    {
        foreach ($this->useImports as $useImport) {
            if ($fullyQualifiedObjectType->equals($useImport)) {
                return \true;
            }
        }
        foreach ($this->constantImports as $constantImport) {
            if ($fullyQualifiedObjectType->equals($constantImport)) {
                return \true;
            }
        }
        $found = \false;
        foreach ($this->functionImports as $type) {
            if ($fullyQualifiedObjectType->equals($type)) {
                $found = \true;
                break;
            }
        }
        return $found;
    }
}
