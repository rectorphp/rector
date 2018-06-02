<?php declare (strict_types=1);

namespace Rector\AppBundle\Controller;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1Controller extends SymfonyController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return ['someKey' => 'someValue'];
    }
}
