<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\FixtureAllClasses;

#[\Immutable]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\FixtureAllClasses;

#[\Immutable]
#[\AllowDynamicProperties]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
