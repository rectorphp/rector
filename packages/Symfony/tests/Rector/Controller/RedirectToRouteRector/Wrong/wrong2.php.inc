<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController2 extends SymfonyController
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something', ['id' => $id]));
    }
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController2 extends SymfonyController
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id]);
    }
}

?>
