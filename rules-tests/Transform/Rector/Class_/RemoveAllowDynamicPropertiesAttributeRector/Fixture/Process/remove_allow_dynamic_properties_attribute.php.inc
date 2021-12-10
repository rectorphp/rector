<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\AllowDynamicProperties]
class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

class WantsAttributeRemoved
{
    public string $someProperty = 'hello world';
}

?>
