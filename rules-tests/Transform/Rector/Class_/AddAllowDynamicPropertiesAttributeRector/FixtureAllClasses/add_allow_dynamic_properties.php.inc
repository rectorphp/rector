<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\FixtureAllClasses;

class WantsAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\FixtureAllClasses;

#[\AllowDynamicProperties]
class WantsAttribute
{
    public string $someProperty = 'hello world';
}

?>
