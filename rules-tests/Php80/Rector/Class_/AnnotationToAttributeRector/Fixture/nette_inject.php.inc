<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteInject
{
    /**
     * @inject
     * @var SomeType
     */
    public $someDependency;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteInject
{
    /**
     * @var SomeType
     */
    #[\Nette\DI\Attributes\Inject]
    public $someDependency;
}

?>
