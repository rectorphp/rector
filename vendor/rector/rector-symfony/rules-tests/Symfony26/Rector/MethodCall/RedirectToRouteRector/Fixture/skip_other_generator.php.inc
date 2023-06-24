<?php

namespace Rector\Symfony\Tests\Symfony26\Rector\MethodCall\RedirectToRouteRector\Fixture;

use Rector\Symfony\Tests\Rector\MethodCall\RedirectToRouteRector\Fixture\SomeGenerator;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SkipController extends AbstractController
{
    public function someAction(SomeGenerator $generator)
    {
        return $this->redirect($generator->generateUrl());
    }
}

?>
