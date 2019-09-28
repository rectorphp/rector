<?php declare(strict_types=1);

namespace Symfony\Component\Validator\Constraints;

if (class_exists('Symfony\Component\Validator\Constraints\Url')) {
    return;
}

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Url extends Constraint
{
    public $message = 'This value is not a valid URL.';

    public $protocols = ['http', 'https'];

    public $relativeProtocol = false;
    public $normalizer;
}
