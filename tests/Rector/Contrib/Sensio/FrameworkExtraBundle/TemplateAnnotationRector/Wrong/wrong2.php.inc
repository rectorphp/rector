<?php declare (strict_types=1);

namespace Rector\AppBundle\Controller;

class ClassWithNamedService1Controller extends Controller
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return ['someKey' => 'someValue'];
    }
}