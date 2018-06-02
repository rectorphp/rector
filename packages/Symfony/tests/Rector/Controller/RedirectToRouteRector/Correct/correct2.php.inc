<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\Source\SymfonyController;

class AppController extends SymfonyController
{
    public function someAction()
    {
        return $this->redirectToRoute('something', ['id' => $id]);
    }
}
