<?php

declare (strict_types=1);
namespace Rector\Nette\Set;

use Rector\Set\Contract\SetListInterface;
final class ContributteSetList implements \Rector\Set\Contract\SetListInterface
{
    /**
     * @var string
     */
    public const APITTE_ATTRIBUTES = __DIR__ . '/../../config/sets/contributte/apitte-attributes.php';
}
