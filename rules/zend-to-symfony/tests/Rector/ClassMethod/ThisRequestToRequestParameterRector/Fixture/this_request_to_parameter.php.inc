<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisRequestToRequestParameterRector\Fixture;

class ThisRequestToParameter extends \Zend_Controller_Action
{
    public function someAction()
    {
        $isGet = $this->_request->isGet();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisRequestToRequestParameterRector\Fixture;

class ThisRequestToParameter extends \Zend_Controller_Action
{
    public function someAction(\Symfony\Component\HttpFoundation\Request $request)
    {
        $isGet = $request->isGet();
    }
}

?>
