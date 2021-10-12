<?php

declare (strict_types=1);
namespace Rector\Nette\Set;

use Rector\Set\Contract\SetListInterface;
final class NetteSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const NETTE_REMOVE_INJECT = __DIR__ . '/../../config/sets/nette-remove-inject.php';
    /**
     * @var string
     */
    public const NETTE_24 = __DIR__ . '/../../config/sets/nette-24.php';
    /**
     * @var string
     */
    public const NETTE_30 = __DIR__ . '/../../config/sets/nette-30.php';
    /**
     * @var string
     */
    public const NETTE_31 = __DIR__ . '/../../config/sets/nette-31.php';
    /**
     * @var string
     */
    public const NETTE_CODE_QUALITY = __DIR__ . '/../../config/sets/nette-code-quality.php';
    /**
     * @var string
     */
    public const NETTE_UTILS_CODE_QUALITY = __DIR__ . '/../../config/sets/nette-utils-code-quality.php';
    /**
     * @var string
     */
    public const ANNOTATIONS_TO_ATTRIBUTES = __DIR__ . '/../../config/sets/nette/annotations-to-attributes.php';
}
