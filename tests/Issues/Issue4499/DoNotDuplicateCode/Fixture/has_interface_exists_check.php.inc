<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode\Fixture;

if (interface_exists('Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode\Fixture\Collection')) {
    return;
}

interface Collection
{

}
