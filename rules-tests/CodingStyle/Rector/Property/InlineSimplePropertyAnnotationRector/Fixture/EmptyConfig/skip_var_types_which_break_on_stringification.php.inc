<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class SkipVarTypesWhichBreakOnStringification
{
    /**
     * @phpstan-var 'week'|'month'|'year'
     */
    private string $interval;
}
?>
-----
<?php

namespace Rector\Tests\CodingStyle\Rector\Property\InlineSimplePropertyAnnotationRector\Fixture\EmptyConfig;

final class SkipVarTypesWhichBreakOnStringification
{
    /**
     * @phpstan-var 'week'|'month'|'year'
     */
    private string $interval;
}
?>
