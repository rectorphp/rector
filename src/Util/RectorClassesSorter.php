<?php

declare (strict_types=1);
namespace Rector\Util;

use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
/**
 * This class ensure the PostRectorInterface class names listed after main RectorInterface class names
 */
final class RectorClassesSorter
{
    /**
     * @param array<class-string<RectorInterface|PostRectorInterface>> $rectorClasses
     * @return array<class-string<RectorInterface|PostRectorInterface>>
     */
    public static function sort(array $rectorClasses): array
    {
        $rectorClasses = array_unique($rectorClasses);
        $mainRector = array_filter($rectorClasses, fn(string $rectorClass): bool => is_a($rectorClass, RectorInterface::class, \true));
        sort($mainRector);
        $postRector = array_filter($rectorClasses, fn(string $rectorClass): bool => is_a($rectorClass, PostRectorInterface::class, \true));
        sort($postRector);
        return array_merge($mainRector, $postRector);
    }
}
