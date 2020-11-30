<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class SkipAlreadyEqual
{
    public function run(Builder $query)
    {
        $dateTime = Carbon::now();
        $query->whereDate('created_at', '<=', $dateTime);
    }
}
