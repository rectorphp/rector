<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector\Fixture;

class PaymentsController extends \AppController
{
    public function index()
    {
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPImplicitRouteToExplicitRouteAnnotationRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;
class PaymentsController extends \AppController
{
    /**
     * @Route(path="/payments/index", name="payments_index")
     */
    public function index()
    {
    }
}

?>
