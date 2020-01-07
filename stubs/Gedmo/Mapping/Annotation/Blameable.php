<?php declare(strict_types=1);

namespace Gedmo\Mapping\Annotation;

if (class_exists('Gedmo\Mapping\Annotation\Blameable')) {
    return;
}

/**
 * @Annotation
 */
class Blameable
{
    /**
     * @var string
     */
    public $on = 'update';

    /**
     * @var mixed
     */
    public $field;

    /**
     * @var mixed
     */
    public $value;
}
