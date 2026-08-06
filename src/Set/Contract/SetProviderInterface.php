<?php

declare (strict_types=1);
namespace Rector\Set\Contract;

/**
 * @deprecated Bond the rules themselves instead, by implementing the ComposerPackageConstraintInterface. A set
 * described as an object only existed to be matched against the installed packages; a bonded rule states the exact
 * package version its target API is available from and applies from there upwards, so a plain set file is enough.
 *
 * @see \Rector\VersionBonding\Contract\ComposerPackageConstraintInterface
 * @see https://github.com/rectorphp/rector-src/pull/8296
 */
interface SetProviderInterface
{
    /**
     * @return SetInterface[]
     */
    public function provide(): array;
}
