<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class AnnotationConvertedAndKeepDocblock
{
    /**
     * @inject
     * @AlternativeApproach
     * @var SomeType
     */
    public $someDependency;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class AnnotationConvertedAndKeepDocblock
{
    /**
     * @AlternativeApproach
     * @var SomeType
     */
    #[\Nette\DI\Attributes\Inject]
    public $someDependency;
}

?>
