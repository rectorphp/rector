<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4652\DoNotChangeExplicitCommentFormat\Fixture;

/**
 * @return (0)
 */
function one(int $value)
{
}

/**
 * @return ""|"0"
 */
function two(int $value)
{
}

one(1);
two(2);
