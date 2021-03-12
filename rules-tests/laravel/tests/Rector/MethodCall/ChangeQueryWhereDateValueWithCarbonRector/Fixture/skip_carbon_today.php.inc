<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class SkipCarbonToday
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', '>', Carbon::today());
    }
}
