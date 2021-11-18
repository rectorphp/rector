<?php

namespace Rector\Tests\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 */
final class NullableProperty
{
    /**
     * @var string
     */
    public ?string $name = null;
}

?>
-----
<?php

namespace Rector\Tests\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @Annotation
 * @\Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor
 */
final class NullableProperty
{
    public function __construct(public string $name)
    {
    }
}

?>
