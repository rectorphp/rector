<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveUnusedVariableAlwaysElse\Fixture;

final class Fixture
{
    public function run(Request $request)
    {
        $path = $request->get('path', null);
        $url  = $request->get('url', null);
        if (null !== $path) {
            return response()->file($path);
        } elseif (null !== $url) {
            return response()->getFile($url);
        }
        throw new \Exception('Error');
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\RemoveUnusedVariableAlwaysElse\Fixture;

final class Fixture
{
    public function run(Request $request)
    {
        $path = $request->get('path', null);
        $url  = $request->get('url', null);
        if (null !== $path) {
            return response()->file($path);
        }
        if (null !== $url) {
            return response()->getFile($url);
        }
        throw new \Exception('Error');
    }
}

?>
