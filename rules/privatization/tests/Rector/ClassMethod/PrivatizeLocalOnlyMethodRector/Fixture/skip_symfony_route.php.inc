<?php

namespace Rector\Privatization\Tests\Rector\ClassMethod\PrivatizeLocalOnlyMethodRector\Fixture;

use Symfony\Component\Routing\Annotation\Route;

class SkipSymfonyRoute
{
    /** @Route("", name="homepage") */
    public function homeAction() : Response
    {
    }
}
