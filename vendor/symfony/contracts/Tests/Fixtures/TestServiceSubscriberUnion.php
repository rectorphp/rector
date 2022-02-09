<?php

namespace RectorPrefix20220209\Symfony\Contracts\Tests\Fixtures;

use RectorPrefix20220209\Symfony\Contracts\Service\ServiceSubscriberTrait;
class TestServiceSubscriberUnion
{
    use ServiceSubscriberTrait;
    private function method1() : \RectorPrefix20220209\Symfony\Contracts\Tests\Fixtures\Service1
    {
        return $this->container->get(__METHOD__);
    }
    /**
     * @return \Symfony\Contracts\Tests\Fixtures\Service1|\Symfony\Contracts\Tests\Fixtures\Service2
     */
    private function method2()
    {
        return $this->container->get(__METHOD__);
    }
    /**
     * @return \Symfony\Contracts\Tests\Fixtures\Service1|\Symfony\Contracts\Tests\Fixtures\Service2|null
     */
    private function method3()
    {
        return $this->container->get(__METHOD__);
    }
}
