<?php

namespace Rector\Core\Tests\Issues\IssueOrIfExplicitBoolCompare\Fixture;

class Fixture
{
    /**
     * @param string $a
     */
    public function run($a = '"', $b)
    {
        if (! $a || ! $b) {
            return;
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueOrIfExplicitBoolCompare\Fixture;

class Fixture
{
    /**
     * @param string $a
     */
    public function run($a = '"', $b)
    {
        if (! $a) {
            return;
        }
        if (! $b) {
            return;
        }
    }
}

?>
