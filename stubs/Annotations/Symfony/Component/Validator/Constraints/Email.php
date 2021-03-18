<?php

declare(strict_types=1);

namespace Symfony\Component\Validator\Constraints;

if (class_exists('Symfony\Component\Validator\Constraints\Email')) {
    return;
}

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Email extends Constraint
{
    public const VALIDATION_MODE_HTML5 = 'html5';
    public const VALIDATION_MODE_STRICT = 'strict';
    public const VALIDATION_MODE_LOOSE = 'loose';

    const INVALID_FORMAT_ERROR = 'bd79c0ab-ddba-46cc-a703-a7a4b08de310';

    protected static $errorNames = [
        self::INVALID_FORMAT_ERROR => 'STRICT_CHECK_FAILED_ERROR',
    ];

    /**
     * @var string[]
     *
     * @internal
     */
    public static $validationModes = [
        self::VALIDATION_MODE_HTML5,
        self::VALIDATION_MODE_STRICT,
        self::VALIDATION_MODE_LOOSE,
    ];

    public $message = 'This value is not a valid email address.';
    public $mode;
    public $normalizer;

    public function __construct($options = null)
    {
    }
}
