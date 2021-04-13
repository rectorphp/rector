<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture;

use Annotation\NotFoundErrorResponse;
use Annotation\OpenApi;

final class AnEntityWithAnEmbeddedWithoutSpaces
{
    /**
     * @NotFoundErrorResponse("collection"),
     *
     * @OpenApi("
     *      summary: Delete collection
     * ")
     */
    private $embedded;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Annotation\NotFoundErrorResponse
