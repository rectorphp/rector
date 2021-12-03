<?php

declare(strict_types=1);

namespace Symfony\Component\Validator\Constraints;

if (class_exists('Symfony\Component\Validator\Constraints\Length')) {
    return;
}

final class Length
{
    public function __construct(
        int $min,
        int $max = null,
        string $maxMessage = null,
        bool $allowed
    ) {
    }

}
