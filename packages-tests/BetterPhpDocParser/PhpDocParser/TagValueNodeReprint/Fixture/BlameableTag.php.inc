<?php

declare(strict_types=1);

namespace Rector\Tests\BetterPhpDocParser\PhpDocParser\TagValueNodeReprint\Fixture\Blameable;

use Gedmo\Mapping\Annotation as Gedmo;

final class BlameableTag
{
    /**
     * @Gedmo\Blameable(on="create")
     */
    protected $gitoliteName;
}

?>
-----
PhpParser\Node\Stmt\Property
-----
Gedmo\Mapping\Annotation\Blameable
