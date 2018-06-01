<?php declare (strict_types=1);

use Rector\Symfony\Tests\Rector\FrameworkBundle\Source\SymfonyController;

class ClassWithNamedService extends SymfonyController
{
    public function render()
    {
        $someService = $this->get('some_service');

        $this->renderTwig([
            'posts' => $this->get('some_service')->callMe()
        ]);
    }
}
