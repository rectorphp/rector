<?php

declare(strict_types=1);

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source;

abstract class ParentController
{
    private $isExperiment = false;


    public function response()
    {
        if ($this->isExperiment === true) {
            return $this->responseNew();
        }
        return $this->responseLegacy();
    }


    abstract public function responseLegacy();
    abstract public function responseNew();
}
