<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class JMSSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/jms/annotations-to-attributes.php';
}
