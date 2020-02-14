<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector\Fixture;

class GetParamToRequest extends \Zend_Controller_Action
{
    public function someAction()
    {
        $tmpId = $this->getParam('tmp_id');

        return $tmpId + 5;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\GetParamToClassMethodParameterAndRouteRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;
class GetParamToRequest extends \Zend_Controller_Action
{
    /**
     * @Route(path="/rector-zend-to-symfony-tests-rector-class-method-get-param-to-class-method-parameter-and-route-rector-fixture-get-param-to-request/some/{tmpId}")
     */
    public function someAction($tmpId): \Symfony\Component\HttpFoundation\Response
    {
        return $tmpId + 5;
    }
}

?>
