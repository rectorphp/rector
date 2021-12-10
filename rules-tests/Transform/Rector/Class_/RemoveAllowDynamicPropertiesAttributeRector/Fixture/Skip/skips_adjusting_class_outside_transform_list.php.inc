<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Skip;

#[\AllowDynamicProperties]
class DoesNothingOnExcludedClass
{
    public string $someProperty = 'hello world';
}

?>
