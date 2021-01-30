<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NettePersistent
{
    /**
     * @persistent
     */
    public function run()
    {
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\Class_\AnnotationToAttributeRector\Fixture;

final class NettePersistent
{
    #[\Nette\Application\Attributes\Persistent]
    public function run()
    {
    }
}

?>
