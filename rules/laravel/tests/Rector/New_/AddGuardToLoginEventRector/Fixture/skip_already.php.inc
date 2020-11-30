<?php

namespace Rector\Laravel\Tests\Rector\New_\AddGuardToLoginEventRector\Fixture;

use Illuminate\Auth\Events\Login;

final class SkipAlready
{
    public function run($guard): void
    {
        $loginEvent = new Login($guard, 'user', false);
    }
}
