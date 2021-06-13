<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteNested
{
    /**
     * @Route(entityClasses={
     *     'one',
     *     'two',
     * }, discriminator="changeReason")
     */
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRouteNested
{
    #[\Symfony\Component\Routing\Annotation\Route(entityClasses: ['one', 'two'], discriminator: 'changeReason')]
    public function action()
    {
    }
}

?>
