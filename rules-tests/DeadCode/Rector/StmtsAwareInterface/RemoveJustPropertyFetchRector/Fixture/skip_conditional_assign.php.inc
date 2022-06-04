<?php

namespace Rector\Tests\DeadCode\Rector\StmtsAwareInterface\RemoveJustPropertyFetchRector\Fixture;

final class SkipConditionalAssign
{
    private $id;

    public function run(?int $id)
    {
        if ($id === null) {
            $id = $this->id;
        }

        return $id;
    }
}
