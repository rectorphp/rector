<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController3 extends SymfonyController
{
    public function someAction()
    {
        return $this->redirect($this->generateUrl('something', ['id' => $id]), 301);
    }
}

?>
-----
<?php

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController3 extends SymfonyController
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id], 301);
    }
}

?>
