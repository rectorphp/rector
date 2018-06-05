<?php declare(strict_types=1);

namespace App\Controller;

use Symfony\Component\HttpKernel\Tests\Controller\AbstractController;

final class SomeController extends AbstractController
{
    public function demo()
    {
        $someService = $this->get('some_service');

        $someData = $someService->someMethod();

        // render $someData etc.
    }
}
