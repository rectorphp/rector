<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

use Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Source\SomeService;

final class SkipConstructAssign
{
    /**
     * @var SomeService
     */
    private $someService;

    public function __construct(SomeService $someService)
    {
        $this->someService = $someService;
    }

    public function run()
    {
        if ($this->someService->whatever()) {
            return true;
        }

        return false;
    }
}
