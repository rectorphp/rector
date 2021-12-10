<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\AllowDynamicProperties, \Immutable]
class RemoveAppendedAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\Immutable]
class RemoveAppendedAttribute
{
    public string $someProperty = 'hello world';
}

?>
