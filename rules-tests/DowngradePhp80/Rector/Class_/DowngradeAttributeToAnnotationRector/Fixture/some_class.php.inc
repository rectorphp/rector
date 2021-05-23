<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoute
{
    #[Route(path: '/path', name: 'action')]
    public function action()
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp80\Rector\Class_\DowngradeAttributeToAnnotationRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SymfonyRoute
{
    /**
     * @\Symfony\Component\Routing\Annotation\Route(path="/path", name="action")
     */
    public function action()
    {
    }
}

?>
