<?php

declare(strict_types=1);

namespace App\Entity;

use Symfony\Component\Validator\Constraints as Assert;

final class SkipAnnotationCallback
{
    /**
     * @Assert\Choice(callback={Invoice::class, "typeChoices"})
     */
    private ?string $type;

    /**
     * @Assert\Choice(callback={Invoice::class, "typeChoices2"})
     */
    private ?string $type2;
}

?>
