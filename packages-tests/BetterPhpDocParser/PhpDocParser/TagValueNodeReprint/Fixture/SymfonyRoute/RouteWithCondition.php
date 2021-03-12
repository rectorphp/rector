<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\SymfonyRoute;

use Symfony\Component\Routing\Annotation\Route;

final class RouteWithCondition
{
    /**
     * @Route(
     *     path="/remove/{object}/{when}/{slot}",
     *     name="route_name",
     *     requirements={"object"="\d{1,9}", "when"="\d{4}-\d{2}-\d{2}", "slot"="\d{1,9}"},
     *     options={"expose"=true, "i18n"=false},
     *     condition="request.isXmlHttpRequest()"
     * )
     */
    public function run()
    {
    }
}
