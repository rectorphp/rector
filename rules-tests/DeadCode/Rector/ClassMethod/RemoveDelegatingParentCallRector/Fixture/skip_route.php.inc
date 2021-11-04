<?php

namespace Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;
use Rector\Tests\DeadCode\Rector\ClassMethod\RemoveDelegatingParentCallRector\Source\ParentController;

class TestController extends ParentController
{
    private $isExperiment = true;

    /**
     * @Route("/test")
     */
    public function response()
    {
        return parent::response();
    }

    public function responseLegacy()
    {
    }
    public function responseNew()
    {
    }
}
