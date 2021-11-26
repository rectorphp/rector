<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture;

#[\Immutable]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture;

#[\Immutable]
#[\AllowDynamicProperties]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
