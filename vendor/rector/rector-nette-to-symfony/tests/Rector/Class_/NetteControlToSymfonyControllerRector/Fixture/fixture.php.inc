<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use Nette\Application\UI\Control;

class SomeControl extends Control
{
    public function render()
    {
        $this->template->param = 'some value';
        $this->template->render(__DIR__ . '/poll.latte');
    }
}

?>
-----
<?php

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use Nette\Application\UI\Control;

class SomeController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    public function action(): \Symfony\Component\HttpFoundation\Response
    {
        return $this->render(__DIR__ . '/poll.latte', ['param' => 'some value']);
    }
}

?>
