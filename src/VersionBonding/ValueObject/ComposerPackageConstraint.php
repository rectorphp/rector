<?php

declare (strict_types=1);
namespace Rector\VersionBonding\ValueObject;

/**
 * @api used by extensions
 */
final class ComposerPackageConstraint
{
    /**
     * @readonly
     */
    private string $packageName;
    /**
     * @readonly
     */
    private string $constraint;
    public function __construct(string $packageName, string $constraint)
    {
        $this->packageName = $packageName;
        $this->constraint = $constraint;
    }
    public function getPackageName(): string
    {
        return $this->packageName;
    }
    public function getConstraint(): string
    {
        return $this->constraint;
    }
}
