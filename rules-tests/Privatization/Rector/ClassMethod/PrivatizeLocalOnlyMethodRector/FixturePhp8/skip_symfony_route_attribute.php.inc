<?php

namespace Rector\Tests\Privatization\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\FixturePhp8;

use Symfony\Component\Routing\Annotation\Route;

final class SkipSymfonyRouteAttribute
{
    #[Route(path: '', name: 'asymfonypageexamplerouting')]
    public function asymfonypageexampleAction() : Response
    {
    }
}
