<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueChangeAndIfAndDateTimeAndDeMorgan\Fixture;

class Fixture
{
    public function run($a, $b, $c)
    {
        if (($a === 'a' || $b === 'b') && $c) {
            $d = 'd';
        }
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Core\Tests\Issues\IssueChangeAndIfAndDateTimeAndDeMorgan\Fixture;

class Fixture
{
    public function run($a, $b, $c)
    {
        if ($a !== 'a' && $b !== 'b') {
            return;
        }
        if (!$c) {
            return;
        }
        $d = 'd';
    }
}

?>
