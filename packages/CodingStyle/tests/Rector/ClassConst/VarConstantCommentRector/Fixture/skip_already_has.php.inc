<?php

namespace Rector\CodingStyle\Tests\Rector\ClassConst\VarConstantCommentRector\Fixture;

final class SkipAlreadyHas
{
    /**
     * @var string
     *
     * It assumes that there is at least one space after the package name.
     *
     * It covers:
     * - "[package-name] "Message => package-name
     * - "[aliased-package-name] "Message => aliased-package-name
     * - "[Aliased\PackageName] "Message => Aliased\PackageName
     */
    public const PACKAGE_NAME_PATTERN = '#\[(?<package>[-\w\\\\]+)\]( ){1,}#';
}
