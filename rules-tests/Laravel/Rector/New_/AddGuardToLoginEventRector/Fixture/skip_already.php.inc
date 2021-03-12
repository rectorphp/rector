<?php

namespace Rector\Tests\Laravel\Rector\New_\AddGuardToLoginEventRector\Fixture;

use Illuminate\Auth\Events\Login;

final class SkipAlready
{
    public function run($guard): void
    {
        $loginEvent = new Login($guard, 'user', false);
    }
}
