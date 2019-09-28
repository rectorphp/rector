<?php declare(strict_types=1);

namespace Symfony\Component\Validator\Constraints;

if (class_exists('Symfony\Component\Validator\Constraints\Type')) {
    return;
}

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY", "METHOD", "ANNOTATION"})
 */
class Type extends Constraint
{
    const INVALID_TYPE_ERROR = 'ba785a8c-82cb-4283-967c-3cf342181b40';

    protected static $errorNames = [
        self::INVALID_TYPE_ERROR => 'INVALID_TYPE_ERROR',
    ];

    public $message = 'This value should be of type {{ type }}.';
    public $type;

    /**
     * {@inheritdoc}
     */
    public function getDefaultOption()
    {
        return 'type';
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredOptions()
    {
        return ['type'];
    }
}
