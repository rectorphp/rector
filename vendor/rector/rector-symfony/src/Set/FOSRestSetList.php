<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api used in public
 */
final class FOSRestSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/fosrest/annotations-to-attributes.php';
}
