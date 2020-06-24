<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use Nette\Application\UI\Control;

class GetPresenterSession extends Control
{
    public function render(): void
    {
        $someSessionSection = $this->getPresenter()->getSession('some_name');

        $this->render();
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\NetteToSymfony\Tests\Rector\Class_\NetteControlToSymfonyControllerRector\Fixture;

use Nette\Application\UI\Control;

class GetPresenterSessionController extends \Symfony\Bundle\FrameworkBundle\Controller\AbstractController
{
    private \Nette\Http\Session $session;
    public function __construct(\Nette\Http\Session $session)
    {
        $this->session = $session;
    }
    public function action(): \Symfony\Component\HttpFoundation\Response
    {
        $someSessionSection = $this->session->getSession('some_name');

        return $this->render();
    }
}

?>
