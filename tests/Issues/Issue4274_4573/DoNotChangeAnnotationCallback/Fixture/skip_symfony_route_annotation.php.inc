<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4274_4573\DoNotChangeAnnotationCallback\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class Controller
{
    /**
     * @Route("/route/{view}.{_format}", name="views.test", defaults={"_format": "json"}, methods={"POST"})
     */
    public function updateAction()
    {
        return 'oops';
    }
}