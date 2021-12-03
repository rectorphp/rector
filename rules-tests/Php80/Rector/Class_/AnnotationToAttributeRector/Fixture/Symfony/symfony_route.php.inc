<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoute
{
    /**
     * @Route("/path", name=EntityColumnAndAssertChoice::class)
     */
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\Class_\AnnotationToAttributeRector\Fixture\Symfony;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoute
{
    #[Route(path: '/path', name: EntityColumnAndAssertChoice::class)]
    public function action()
    {
    }
}

?>
