<?php

namespace Rector\Laravel\Tests\Rector\FuncCall\HelperFuncCallToFacadeClassRector\Fixture;

class SkipWithArgs
{
    public function run()
    {
        return app('translator', 'some');
    }
}
