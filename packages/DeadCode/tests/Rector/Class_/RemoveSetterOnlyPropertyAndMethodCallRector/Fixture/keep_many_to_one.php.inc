<?php

namespace Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Fixture;

use Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Source\PostTemplate;

use Doctrine\ORM\Mapping as ORM;

class KeepManyToOne
{
    /**
     * @var PostTemplate
     * @ORM\ManyToOne(targetEntity="Rector\DeadCode\Tests\Rector\Class_\RemoveSetterOnlyPropertyAndMethodCallRector\Source\PostTemplate", inversedBy="postTemplateBuildings", cascade={"persist", "merge"})
     */
    private $postTemplate;

    public function setPostTemplate(PostTemplate $postTemplate)
    {
        $this->postTemplate = $postTemplate;
    }
}
