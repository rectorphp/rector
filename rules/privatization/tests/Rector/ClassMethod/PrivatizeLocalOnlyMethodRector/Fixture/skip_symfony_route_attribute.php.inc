<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class FrontController
{
    #[Route(path: '', name: 'homepage')]
    public function homeAction() : Response
    {
    }
}
