<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue5267\DoNotThrowShouldNotHappenException\Fixture;

use Rector\Core\Tests\Issues\Issue5267\DoNotThrowShouldNotHappenException\Source\Xyz;

class SkipThrowShouldNotHappenException
{
    public function fails(Xyz $xyz): void
    {
        $xyz->abc->get('xxx');
    }
}


?>
