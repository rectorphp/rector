<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteCrossOrigin
{
    /**
     * @crossOrigin
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NetteCrossOrigin
{
    #[\Nette\Application\Attributes\CrossOrigin]
    public function run()
    {
    }
}

?>
