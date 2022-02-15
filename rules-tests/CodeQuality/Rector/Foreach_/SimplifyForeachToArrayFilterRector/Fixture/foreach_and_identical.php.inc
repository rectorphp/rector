<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Source\SomeAlbum;

final class ForeachAndIdentical
{
    /**
     * @param SomeAlbum[] $albums
     */
    public function filter(array $albums)
    {
        $activeAlbums = [];
        foreach ($albums as $key => $album) {
            if ($album->type === 'active') {
                $activeAlbums[$key] = $album;
            }
        }

        return $activeAlbums;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Fixture;

use Rector\Tests\CodeQuality\Rector\Foreach_\SimplifyForeachToArrayFilterRector\Source\SomeAlbum;

final class ForeachAndIdentical
{
    /**
     * @param SomeAlbum[] $albums
     */
    public function filter(array $albums)
    {
        $activeAlbums = [];
        $activeAlbums = array_filter($albums, fn($album) => $album->type === 'active');

        return $activeAlbums;
    }
}

?>
