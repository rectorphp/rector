<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

final class CarbonNowWithCall
{
    public function run(Builder $query)
    {
        $query->whereDate('created_at', '>', Carbon::now()->subDays(10000));
    }
}

?>
-----
<?php

namespace Rector\Laravel\Tests\Rector\MethodCall\ChangeQueryWhereDateValueWithCarbonRector\Fixture;

use Carbon\Carbon;
use Illuminate\Database\Query\Builder;

final class CarbonNowWithCall
{
    public function run(Builder $query)
    {
        $dateTime = Carbon::now()->subDays(10000);
        $query->whereDate('created_at', '>=', $dateTime);
        $query->whereTime('created_at', '>=', $dateTime);
    }
}

?>
