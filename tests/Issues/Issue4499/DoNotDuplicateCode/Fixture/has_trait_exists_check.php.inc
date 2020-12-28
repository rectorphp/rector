<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode\Fixture;

if (trait_exists('Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode\Fixture\TimestampableEntity')) {
    return;
}

trait TimestampableEntity
{

}
