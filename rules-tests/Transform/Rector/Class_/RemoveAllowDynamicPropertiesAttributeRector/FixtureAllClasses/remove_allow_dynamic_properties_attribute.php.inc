<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\FixtureAllClasses;

#[\AllowDynamicProperties]
class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\FixtureAllClasses;

class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
