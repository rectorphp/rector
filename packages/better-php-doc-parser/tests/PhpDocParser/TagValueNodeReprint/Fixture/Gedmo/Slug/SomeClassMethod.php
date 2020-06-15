<?php

declare(strict_types=1);

namespace Rector\BetterPhpDocParser\Tests\PhpDocParser\TagValueNodeReprint\Fixture\Gedmo\Slug;

use Gedmo\Mapping\Annotation as Gedmo;

final class SomeClassMethod
{
    /**
     * @Gedmo\Slug(fields={"lastName", "firstName"}, prefix="papo-")
     */
    protected $gitoliteName;
}
