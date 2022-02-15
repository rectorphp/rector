<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\FixturePhp73;

use Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Source\SomeAlbum;

final class SkipDifferentKey
{
    /**
     * @param SomeAlbum[] $albums
     */
    public function filter(array $albums)
    {
        $activeAlbums = [];
        foreach ($albums as $key => $album) {
            if ($album->type === 'active') {
                $activeAlbums[$key + 1] = $album;
            }
        }

        return $activeAlbums;
    }
}
