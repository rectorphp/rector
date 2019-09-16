<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector\Fixture;

class ZendController extends \Zend_Controller_Action
{
    public function someAction()
    {
    }
}

?>
-----
<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Class_\ChangeZendControllerClassToSymfonyControllerClassRector\Fixture;

final class ZendController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function someAction()
    {
    }
}

?>
