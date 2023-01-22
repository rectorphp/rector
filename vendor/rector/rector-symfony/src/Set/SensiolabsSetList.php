<?php

declare (strict_types=1);
namespace Rector\Symfony\Set;

use Rector\Set\Contract\SetListInterface;
/**
 * @api
 */
final class SensiolabsSetList implements SetListInterface
{
    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_40 = __DIR__ . '/../../config/sets/sensiolabs/framework-extra-40.php';
    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_50 = __DIR__ . '/../../config/sets/sensiolabs/framework-extra-50.php';
    /**
     * @var string
     */
    public const FRAMEWORK_EXTRA_61 = __DIR__ . '/../../config/sets/sensiolabs/framework-extra-61.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/sensiolabs/annotations-to-attributes.php';
}
