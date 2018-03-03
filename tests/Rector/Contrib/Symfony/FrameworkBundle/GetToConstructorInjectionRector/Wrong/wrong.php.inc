<?php declare (strict_types=1);

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class ClassWithNamedService extends Controller
{
    public function render()
    {
        $someService = $this->get('some_service');

        $this->renderTwig([
            'posts' => $this->get('some_service')->callMe()
        ]);
    }
}
