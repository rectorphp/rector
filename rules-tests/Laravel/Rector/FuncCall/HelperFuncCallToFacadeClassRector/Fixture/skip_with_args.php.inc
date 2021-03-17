<?php

namespace Rector\Tests\Laravel\Rector\FuncCall\HelperFuncCallToFacadeClassRector\Fixture;

class SkipWithArgs
{
    public function run()
    {
        return app('translator', 'some');
    }
}
