<?php

declare (strict_types=1);
namespace Rector\Util;

use Rector\Contract\Rector\RectorInterface;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
final class RectorClassesSorter
{
    /**
     * @param array<class-string<RectorInterface|PostRectorInterface>> $rectorClasses
     * @return array<class-string<RectorInterface>>
     */
    public static function sortAndFilterOutPostRectors(array $rectorClasses): array
    {
        $rectorClasses = array_unique($rectorClasses);
        $mainRectorClasses = array_filter($rectorClasses, fn(string $rectorClass): bool => is_a($rectorClass, RectorInterface::class, \true));
        sort($mainRectorClasses);
        return $mainRectorClasses;
    }
}
