<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Expression\RedirectorToRedirectToUrlRector\Fixture;

class ThisViewToThisRender extends \Zend_Controller_Action
{
    public function someAction()
    {
        $redirector = $this->_helper->redirector;
        $redirector->goToUrl('abc');
    }
}

?>
-----
<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\Expression\RedirectorToRedirectToUrlRector\Fixture;

class ThisViewToThisRender extends \Zend_Controller_Action
{
    public function someAction()
    {
        $redirector = $this->_helper->redirector;
        return $this->redirect('abc');
    }
}

?>
