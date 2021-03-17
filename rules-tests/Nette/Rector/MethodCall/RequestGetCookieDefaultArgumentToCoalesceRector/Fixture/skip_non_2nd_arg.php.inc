<?php

namespace Rector\Tests\Nette\Rector\MethodCall\RequestGetCookieDefaultArgumentToCoalesceRector\Fixture;

use Nette\Http\Request;

class SkipNon2ndArg
{
    public function run(Request $request)
    {
        return $request->getCookie('name');
    }
}
