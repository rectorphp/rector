<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\AllowDynamicProperties]
#[\Immutable]
class RemoveAppendedAttributeGroup
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\Immutable]
class RemoveAppendedAttributeGroup
{
    public string $someProperty = 'hello world';
}

?>
