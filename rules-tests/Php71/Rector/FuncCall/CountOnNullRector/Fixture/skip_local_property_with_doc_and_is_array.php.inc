<?php

namespace Rector\Tests\Php71\Rector\FuncCall\CountOnNullRector\Fixture;

final class SkipLocalPropertyWithDocAndIsArray
{
    /**
     * @var int[]
     */
    private $notTitles;

    public function getTitle()
    {
        $this->titles = null;
        if (is_array($this->titles)) {
            return count($this->titles);
        }
    }
}
