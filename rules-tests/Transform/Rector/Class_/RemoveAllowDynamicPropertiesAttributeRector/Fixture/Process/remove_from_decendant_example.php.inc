<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

class DoesNotUseDynamicProps
{
    public string $someProperty = 'hello world';
}

#[\AllowDynamicProperties]
class ChildHasAttribute extends DoesNotUseDynamicProps
{
    public string $someProperty = 'hello space';
}

class GrandkidOfHasAttribute extends ChildHasAttribute
{
    public string $someProperty = 'hello universe';
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Class_\RemoveAllowDynamicPropertiesAttributeRector\Fixture\Process;

class DoesNotUseDynamicProps
{
    public string $someProperty = 'hello world';
}

class ChildHasAttribute extends DoesNotUseDynamicProps
{
    public string $someProperty = 'hello space';
}

class GrandkidOfHasAttribute extends ChildHasAttribute
{
    public string $someProperty = 'hello universe';
}

?>
