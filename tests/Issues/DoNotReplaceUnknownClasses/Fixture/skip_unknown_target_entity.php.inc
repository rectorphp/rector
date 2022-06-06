<?php

namespace Rector\Core\Tests\Issues\DoNotReplaceUnknownClasses\Fixture;

use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use My\Namespace\UsedUnknownClass;

final class SkipUnknownTargetEntity
{
    /**
     * @ORM\ManyToOne(targetEntity=\My\Namespace\UsedUnknownClass::class)
     */
    private readonly ?Collection $items;
}
