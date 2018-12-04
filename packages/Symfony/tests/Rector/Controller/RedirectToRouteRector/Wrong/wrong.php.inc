<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something'));
    }
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function someAction()
    {
        return $this->redirectToRoute('something');
    }
}

?>
