<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\FixturePhp73;

use Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Source\SomeAlbum;

final class SkipIfNotEmptyArray
{
    /**
     * @param SomeAlbum[] $albums
     */
    public function filter(array $albums)
    {
        $activeAlbums = [100];

        foreach ($albums as $key => $album) {
            if ($album->type === 'active') {
                $activeAlbums[$key] = $album;
            }
        }

        return $activeAlbums;
    }
}
