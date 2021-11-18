<?php

namespace Rector\Tests\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 */
class SomeAnnotation
{
    /**
     * @var string[]
     * @Required()
     */
    public array $enum;
}

?>
-----
<?php

namespace Rector\Tests\Compatibility\Rector\Class_\AttributeCompatibleAnnotationRector\Fixture;

use Doctrine\Common\Annotations\Annotation\Required;

/**
 * @annotation
 * @\Doctrine\Common\Annotations\Annotation\NamedArgumentConstructor
 */
class SomeAnnotation
{
    /**
     * @param string[] $enum
     */
    public function __construct(public array $enum)
    {
    }
}

?>
