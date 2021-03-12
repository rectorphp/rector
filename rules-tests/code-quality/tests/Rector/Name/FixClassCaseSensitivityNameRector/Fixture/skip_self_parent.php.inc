<?php

namespace Rector\CodeQuality\Tests\Rector\Name\FixClassCaseSensitivityNameRector\Fixture;

final class SkipSelfParent
{
    public function run()
    {
        $fatherSon = new self();
    }
}
