<?php

namespace Rector\Core\Tests\Issues\IssueAndIfCompleteDeMorgan\Fixture;

class ParentClass
{
    protected $d;
}

class Fixture extends ParentClass
{
    public function run($a, $b, $c)
    {
        if (($a === 'a' || $b === 'b') && $c) {
            $this->d = 'd';
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\IssueAndIfCompleteDeMorgan\Fixture;

class ParentClass
{
    protected $d;
}

class Fixture extends ParentClass
{
    public function run($a, $b, $c)
    {
        if ($a !== 'a' && $b !== 'b') {
            return;
        }
        if (!$c) {
            return;
        }
        $this->d = 'd';
    }
}

?>
