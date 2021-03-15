<?php

namespace Rector\Tests\CodeQuality\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

final class SkipSelfParent
{
    public function run()
    {
        $fatherSon = new self();
    }
}
