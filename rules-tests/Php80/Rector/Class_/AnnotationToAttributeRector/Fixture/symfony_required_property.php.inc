<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredProperty
{
    /**
     * @required
     */
    public $someProperty;
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

class SymfonyRequiredProperty
{
    #[\Symfony\Contracts\Service\Attribute\Required]
    public $someProperty;
}

?>
