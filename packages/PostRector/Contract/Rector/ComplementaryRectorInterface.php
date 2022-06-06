<?php

declare (strict_types=1);
namespace Rector\PostRector\Contract\Rector;

/**
 * Marker interface for rules, that with dual along another rule.
 * E.g. RenameClassRector that handles PHP renames, has a complementary rule that handles yaml, neon and configs too.
 */
interface ComplementaryRectorInterface
{
}
