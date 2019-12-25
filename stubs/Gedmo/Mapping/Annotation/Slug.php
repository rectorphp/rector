<?php

declare(strict_types=1);

namespace Gedmo\Mapping\Annotation;

if (class_exists('Gedmo\Mapping\Annotation\Slug')) {
    return;
}

/**
 * @Annotation
 */
class Slug
{
    /** @var array<string> @Required */
    public $fields = array();
    /** @var boolean */
    public $updatable = true;
    /** @var string */
    public $style = 'default'; // or "camel"
    /** @var boolean */
    public $unique = true;
    /** @var string */
    public $unique_base = null;
    /** @var string */
    public $separator = '-';
    /** @var string */
    public $prefix = '';
    /** @var string */
    public $suffix = '';
    public $handlers = array();
    /** @var string */
    public $dateFormat = 'Y-m-d-H:i';
}
