<?php

declare (strict_types=1);
namespace Rector\Set\Enum;

/**
 * @api used in sets
 */
final class SetGroup
{
    /**
     * @var string
     */
    public const CORE = 'core';
    /**
     * @var string
     */
    public const PHP = 'php';
    /**
     * @var string
     */
    public const TWIG = 'twig';
    /**
     * @var string
     */
    public const PHPUNIT = 'phpunit';
    /**
     * @var string
     */
    public const DOCTRINE = 'doctrine';
    /**
     * @var string
     */
    public const SYMFONY = 'symfony';
    /**
     * @var string
     */
    public const ATTRIBUTES = 'attributes';
}
