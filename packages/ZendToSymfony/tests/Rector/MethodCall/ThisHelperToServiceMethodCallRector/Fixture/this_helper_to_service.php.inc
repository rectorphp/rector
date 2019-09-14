<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\MethodCall\ThisHelperToServiceMethodCallRector\Fixture;

class ThisHelperToService extends \Zend_Controller_Action
{
    /**
     * @var \Zend_Controller_Action_HelperBroker
     */
    private $_helper;

    public function someAction()
    {
        $this->_helper->onlinePayment(1000);

        $this->_helper->onlinePayment()->isPaid();
    }
}

?>
-----
<?php declare(strict_types=1);

namespace Rector\ZendToSymfony\Tests\Rector\MethodCall\ThisHelperToServiceMethodCallRector\Fixture;

class ThisHelperToService extends \Zend_Controller_Action
{
    /**
     * @var \Zend_Controller_Action_HelperBroker
     */
    private $_helper;
    /**
     * @var \Zend_Controller_Action_Helper_OnlinePayment
     */
    private $onlinePaymentHelper;
    public function __construct(\Zend_Controller_Action_Helper_OnlinePayment $onlinePaymentHelper)
    {
        $this->onlinePaymentHelper = $onlinePaymentHelper;
    }

    public function someAction()
    {
        $this->onlinePaymentHelper->direct(1000);

        $this->onlinePaymentHelper->direct()->isPaid();
    }
}

?>
