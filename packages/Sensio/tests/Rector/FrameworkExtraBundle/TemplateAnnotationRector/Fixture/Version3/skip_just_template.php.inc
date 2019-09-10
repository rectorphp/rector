<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Fixture;

use Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Source\SymfonyController;

class SkipJustTemplateController extends SymfonyController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
