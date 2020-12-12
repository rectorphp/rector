<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

final class EdgeInArray
{
    public function provideFilters(): array
    {
        return [
            'filter_name' => [$this, 'currency']
        ];
    }

    private function currency($value)
    {
        return $value . ' €';
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

final class EdgeInArray
{
    public function provideFilters(): array
    {
        return [
            'filter_name' => function ($value) {
                return $this->currency($value);
            }
        ];
    }

    private function currency($value)
    {
        return $value . ' €';
    }
}

?>
