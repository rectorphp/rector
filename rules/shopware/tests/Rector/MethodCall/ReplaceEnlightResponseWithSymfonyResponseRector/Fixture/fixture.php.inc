<?php

namespace Rector\Shopware\Tests\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector\Fixture;

class FrontendController
{
    public function run()
    {
        $this->Response()->setHeader('Foo', 'Yea');
        $this->Response()->setHeader('Cache-Control', 'Yea');
        $this->Response()->removeCookie('fooo');
        $this->Response()->setRawHeader('Foo: 1');
        $this->Response()->clearRawHeader('Foo');
        $this->Response()->clearAllHeaders();
        $this->Response()->clearRawHeaders();
        $this->Response()->setCookie('Foo', 1);
    }

    /**
     * @return \Enlight_Controller_Response_Response
     */
    public function Response()
    {
    }
}

?>
-----
<?php

namespace Rector\Shopware\Tests\Rector\MethodCall\ReplaceEnlightResponseWithSymfonyResponseRector\Fixture;

class FrontendController
{
    public function run()
    {
        $this->Response()->headers->set('foo', 'Yea');
        $this->Response()->headers->set('cache-control', 'Yea', true);
        $this->Response()->headers->removeCookie('fooo');
        $this->Response()->headers->set('foo', '1');
        $this->Response()->headers->remove('foo');
        $this->Response()->headers->replace();
        $this->Response()->headers->replace();
        $this->Response()->headers->setCookie(new \Symfony\Component\HttpFoundation\Cookie('Foo', 1));
    }

    /**
     * @return \Enlight_Controller_Response_Response
     */
    public function Response()
    {
    }
}

?>
