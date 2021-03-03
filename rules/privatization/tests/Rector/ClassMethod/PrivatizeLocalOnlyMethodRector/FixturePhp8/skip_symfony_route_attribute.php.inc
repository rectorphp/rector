<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\FixturePhp8;

use Symfony\Component\Routing\Annotation\Route;

final class SkipSymfonyRouteAttribute
{
    #[Route(path: '', name: 'asymfonypageexample')]
    public function asymfonypageexampleAction() : Response
    {
    }
}
