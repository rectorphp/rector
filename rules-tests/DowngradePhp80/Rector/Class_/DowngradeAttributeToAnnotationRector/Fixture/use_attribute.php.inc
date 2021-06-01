<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::IS_REPEATABLE)]
class AutoconfigureTag
{
    public function __construct(string $name = null, array $attributes = [])
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

/**
 * @Attribute
 */
class AutoconfigureTag
{
    public function __construct(string $name = null, array $attributes = [])
    {
    }
}

?>
