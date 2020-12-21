<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfIssetToNullCoalescingRector\Fixture;

final class Fixture
{
    public function run($possibleStatieYamlFile)
    {
        if (isset($possibleStatieYamlFile['imports'])) {
            $possibleStatieYamlFile['imports'] = array_merge($possibleStatieYamlFile['imports'], $filesToImport);
        } else {
            $possibleStatieYamlFile['imports'] = $filesToImport;
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfIssetToNullCoalescingRector\Fixture;

final class Fixture
{
    public function run($possibleStatieYamlFile)
    {
        $possibleStatieYamlFile['imports'] = array_merge($possibleStatieYamlFile['imports'] ?? [], $filesToImport);
    }
}

?>
