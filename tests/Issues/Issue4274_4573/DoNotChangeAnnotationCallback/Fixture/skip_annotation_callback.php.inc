<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\Issue4274_4573\DoNotChangeAnnotationCallback\Fixture;

use Symfony\Component\Validator\Constraints as Assert;

final class SkipAnnotationCallback
{
    /**
     * @Assert\Choice(callback={Invoice::class, "typeChoices"})
     */
    private $type;

    /**
     * @Assert\Choice(callback={Invoice::class, "typeChoices2"})
     */
    private $type2;
}

?>
