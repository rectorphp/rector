<?php declare (strict_types=1);

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class ClassWithNamedService1 extends SymfonyController
{
    /**
     * @Template("someFile.toBe.used")
     */
    public function indexAction()
    {
        return [];
    }
}
