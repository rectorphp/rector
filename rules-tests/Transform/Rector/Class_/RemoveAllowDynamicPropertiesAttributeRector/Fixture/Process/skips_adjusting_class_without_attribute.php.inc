<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

#[\AllowDynamicProperty]
class DoesNothingForDifferentAttribute
{
    public string $someProperty = 'hello world';
}

?>
