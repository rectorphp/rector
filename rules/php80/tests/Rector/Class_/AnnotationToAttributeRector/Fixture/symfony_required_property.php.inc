<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredPropety
{
    /**
     * @required
     */
    public $someProperty;
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredPropety
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public $someProperty;
}

?>
