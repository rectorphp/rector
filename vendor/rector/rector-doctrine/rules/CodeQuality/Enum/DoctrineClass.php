<?php

declare (strict_types=1);
namespace Rector\Doctrine\CodeQuality\Enum;

use Deprecated;
/**
 * @deprecated Switch to @see \Rector\Doctrine\Enum\DoctrineClass instead
 * @api
 */
final class DoctrineClass
{
    /**
     * @var string
     */
    #[Deprecated(message: 'BC only')]
    public const COLLECTION = \Rector\Doctrine\Enum\DoctrineClass::COLLECTION;
}
