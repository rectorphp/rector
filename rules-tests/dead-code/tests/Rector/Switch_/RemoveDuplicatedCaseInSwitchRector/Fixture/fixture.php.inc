<?php

namespace Rector\DeadCode\Tests\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\Fixture;

class Fixture
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
                 return $this->modifyHeader($node, 'replace');
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}

?>
-----
<?php

namespace Rector\DeadCode\Tests\Rector\Switch_\RemoveDuplicatedCaseInSwitchRector\Fixture;

class Fixture
{
    public function run()
    {
        switch ($name) {
             case 'clearHeader':
                 return $this->modifyHeader($node, 'remove');
             case 'clearAllHeaders':
             case 'clearRawHeaders':
                 return $this->modifyHeader($node, 'replace');
             case '...':
                 return 5;
        }
    }
}

?>
