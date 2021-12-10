<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\Immutable]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\Immutable]
#[\AllowDynamicProperties]
class AppendAttribute
{
    public string $someProperty = 'hello world';
}

?>
