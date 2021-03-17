<?php

namespace Rector\Tests\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Fixture;

use Nette\Application\UI\Control;
use Rector\Tests\NetteKdyby\Rector\MethodCall\ReplaceMagicPropertyEventWithEventClassRector\Source\SomeUser;

final class SkipOnSuccessInControl extends Control
{
    public $onSuccess = [];

    public $onBeforeSuccess = [];

    public function run(SomeUser $user)
    {
        $this->onSuccess($user, 'run');

        $this->onBeforeSuccess($user, 'run');
    }
}
