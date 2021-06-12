<?php

namespace Rector\Core\Tests\Issues\Issue6481\Fixture;

class Fixture
{
    public function test(): void {
        $lorem = new \DateTime();
        $ipsum = "";

        if ($lorem instanceof \DateTime) {
            echo "$ipsum";
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Issue6481\Fixture;

class Fixture
{
    public function test(): void {
        $lorem = new \DateTime();
        $ipsum = "";

        echo sprintf('%s', $ipsum);
    }
}

?>
