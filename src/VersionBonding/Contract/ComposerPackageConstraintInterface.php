<?php

declare (strict_types=1);
namespace Rector\VersionBonding\Contract;

use Rector\VersionBonding\ValueObject\ComposerPackageConstraint;
/**
 * Can be implemented by @see \Rector\Contract\Rector\RectorInterface
 *
 * Rules that do not meet this composer package constraint will be skipped.
 *
 * @api used by extensions
 */
interface ComposerPackageConstraintInterface
{
    public function provideComposerPackageConstraint(): ComposerPackageConstraint;
}
