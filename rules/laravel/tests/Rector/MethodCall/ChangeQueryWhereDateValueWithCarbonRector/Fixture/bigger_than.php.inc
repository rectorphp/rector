<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class BiggerThan
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', '>', Carbon::now());
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

class BiggerThan
{
    public function run(Builder $query)
    {
        $dateTime = Carbon::now();
        $query->whereDate('created_at', '>=', $dateTime);
        $query->whereTime('created_at', '>=', $dateTime);
    }
}

?>
