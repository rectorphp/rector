<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

final class ArrayCallWithReturnType
{
    public function provideFilters(): array
    {
        return [
            'filter_name' => [$this, 'currency']
        ];
    }

    public function currency($value): string
    {
        return $value . ' €';
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Array_\ArrayThisCallToThisMethodCallRector\Fixture;

final class ArrayCallWithReturnType
{
    public function provideFilters(): array
    {
        return [
            'filter_name' => function ($value) : string {
                return $this->currency($value);
            }
        ];
    }

    private function currency($value): string
    {
        return $value . ' €';
    }
}

?>
