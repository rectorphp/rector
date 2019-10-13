<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisViewToThisRenderResponseRector\Fixture;

class ThisViewToThisRender extends \Zend_Controller_Action
{
    public function someAction()
    {
        $this->_view->value = 5;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\ClassMethod\ThisViewToThisRenderResponseRector\Fixture;

class ThisViewToThisRender extends \Zend_Controller_Action
{
    public function someAction()
    {
        $templateData = [];
        $templateData['value'] = 5;
        $this->render('...', $templateData);
    }
}

?>
