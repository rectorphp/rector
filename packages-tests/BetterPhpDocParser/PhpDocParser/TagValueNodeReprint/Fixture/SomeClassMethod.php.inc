<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\Gedmo\Slug;

use Gedmo\Mapping\Annotation as Gedmo;

final class SomeClassMethod
{
    /**
     * @Gedmo\Slug(fields={"lastName", "firstName"}, prefix="papo-")
     */
    protected $gitoliteName;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Gedmo\Mapping\Annotation\Slug
