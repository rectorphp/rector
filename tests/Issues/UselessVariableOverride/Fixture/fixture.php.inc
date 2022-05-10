<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\UselessVariableOverride\Fixture;

class Fixture
{
    public function formatPlaces($geotools, $places): array
    {
        $features = \array_map(function (array $placeResource) use (
            $geotools
        ) {
            $distances = \array_map(static function (CoordinateInterface $coordinate) use ($geotools) {
                return $geotools->distance()->flat();
            }, []);

            return new Feature($distances);
        }, $places);

        return $features;
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\UselessVariableOverride\Fixture;

class Fixture
{
    public function formatPlaces($geotools, $places): array
    {
        return \array_map(function (array $placeResource) use (
            $geotools
        ) {
            $distances = \array_map(static function (CoordinateInterface $coordinate) use ($geotools) {
                return $geotools->distance()->flat();
            }, []);

            return new Feature($distances);
        }, $places);
    }
}

?>
