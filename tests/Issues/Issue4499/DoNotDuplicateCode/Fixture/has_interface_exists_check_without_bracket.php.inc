<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4499\DoNotDuplicateCode\Fixture;

if (interface_exists('Doctrine\Common\Collections\Collection'))
    return;

interface Collection
{

}

?>
