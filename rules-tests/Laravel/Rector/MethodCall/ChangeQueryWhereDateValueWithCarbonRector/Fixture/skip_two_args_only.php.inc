<?php

namespace Rector\Tests\Laravel\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class SkipTwoArgsOnly
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', Carbon::now());
    }
}
