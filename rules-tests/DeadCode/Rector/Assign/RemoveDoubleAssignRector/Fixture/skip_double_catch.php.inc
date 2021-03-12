<?php

namespace Rector\DeadCode\Tests\Rector\Assign\RemoveDoubleAssignRector\Fixture;

class SkipDoubleCatch
{
    public function action()
    {
        $noException = true;

        try {
            $this->doSomething();
        } catch (\InvalidArgumentException $e) {
            $noException = false;
        } catch (\Exception $e) {
            $noException = false;
        }
    }
}
