<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector\Fixture;

class MessagesController extends \AppController
{
    public $components = ['Overview'];

    public function someAction()
    {
        $this->Overview->filter();
    }
}

class OverviewComponent extends \Component
{
    public function filter()
    {
    }
}

?>
-----
<?php

namespace Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector\Fixture;

class MessagesController extends \AppController
{
    /**
     * @var \Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector\Fixture\OverviewComponent
     */
    private $overviewComponent;
    public function __construct(\Rector\CakePHPToSymfony\Tests\Rector\Class_\CakePHPControllerComponentToSymfonyRector\Fixture\OverviewComponent $overviewComponent)
    {
        $this->overviewComponent = $overviewComponent;
    }
    public function someAction()
    {
        $this->overviewComponent->filter();
    }
}

class OverviewComponent extends \Component
{
    public function filter()
    {
    }
}

?>
