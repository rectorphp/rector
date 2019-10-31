<?php

namespace Rector\Sensio\Tests\Rector\FrameworkExtraBundle\TemplateAnnotationRector\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class SkipJustTemplateController extends AbstractController
{
    /**
     * @Template
     */
    public function indexAction()
    {
        return [];
    }
}
