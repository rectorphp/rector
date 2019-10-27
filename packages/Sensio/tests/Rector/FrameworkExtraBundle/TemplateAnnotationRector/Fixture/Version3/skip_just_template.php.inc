<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Fixture;

class SkipJustTemplateController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
