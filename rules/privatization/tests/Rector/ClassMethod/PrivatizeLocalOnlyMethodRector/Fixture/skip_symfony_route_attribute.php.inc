<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

final class SkipSymfonyRouteAttribute
{
    #[Route(path: '', name: 'homepage')]
    public function homeAction() : Response
    {
    }
}
