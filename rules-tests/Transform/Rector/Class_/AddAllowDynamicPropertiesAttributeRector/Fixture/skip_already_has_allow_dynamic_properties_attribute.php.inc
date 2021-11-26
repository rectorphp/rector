<?php

namespace Rector\Tests\Transform\Rector\Class_\AddAllowDynamicPropertiesAttributeRector\Fixture;

#[\AllowDynamicProperties]
class AlreadyHasAttribute
{
    public string $someProperty = 'hello world';
}

class ChildOfAlreadyHasAttribute extends AlreadyHasAttribute {
    public string $someProperty = 'hello space';
}

class GrandkidOfAlreadyHasAttribute extends ChildOfAlreadyHasAttribute {
    public string $someProperty = 'hello universe';
}

?>
