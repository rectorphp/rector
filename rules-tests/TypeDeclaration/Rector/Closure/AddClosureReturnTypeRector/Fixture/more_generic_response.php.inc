<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class MoreGenericResponse
{
    public function run()
    {
        $result = function () {
            if (mt_rand(0, 100)) {
                return new RedirectResponse();
            }

            return new Response();
        };
    }
}
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Closure\AddClosureReturnTypeRector\Fixture;

use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

final class MoreGenericResponse
{
    public function run()
    {
        $result = function (): \Symfony\Component\HttpFoundation\Response {
            if (mt_rand(0, 100)) {
                return new RedirectResponse();
            }

            return new Response();
        };
    }
}
