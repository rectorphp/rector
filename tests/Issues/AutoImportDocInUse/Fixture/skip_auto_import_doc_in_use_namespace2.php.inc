<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\AutoImportDocInUse\Fixture;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Rector\Core\Tests\Issues\AutoImportDocInUse\Source\Annotation;

final class SkipAutoImportDocInUseNamespace2 extends AbstractController
{
    /**
     * @Annotation\SomeEnum2()
     *
     * @return Response
     */
    #[Route('/main', name: 'main')]
    public function index(): Response
    {
        return $this->json([
            'message' => 'Welcome to your new controller!',
            'path' => 'src/Controller/MainController.php',
        ]);
    }
}

?>
