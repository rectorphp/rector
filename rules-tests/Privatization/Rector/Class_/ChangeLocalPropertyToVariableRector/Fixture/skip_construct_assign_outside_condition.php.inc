<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Fixture;

use Rector\Tests\Privatization\Rector\Class_\ChangeLocalPropertyToVariableRector\Source\SomeService;

final class SkipConstructAssignOutsideCondition
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
        return $this->someService->whatever();
    }
}
