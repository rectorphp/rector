<?php

namespace Rector\CodeQuality\Tests\Rector\FunctionLike\RemoveAlwaysTrueConditionSetInConstructorRector\Fixture;

final class SkipUnknown
{
    private $yolo;

    public function __construct($yolo)
    {
        $this->yolo = $yolo;
    }

    public function go()
    {
        if ($this->yolo) {
            return 'yes';
        }
    }
}
