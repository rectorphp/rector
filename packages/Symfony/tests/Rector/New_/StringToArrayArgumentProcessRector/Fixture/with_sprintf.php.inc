<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;

class WithSprintf
{
    public function run()
    {
        $process = new Process(sprintf('"%s" -l "%s"', $this->executable, $path));
    }
}

?>
-----
<?php

namespace Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Fixture;

use Rector\Symfony\Tests\Rector\New_\StringToArrayArgumentProcessRector\Source\Process;

class WithSprintf
{
    public function run()
    {
        $process = new Process([$this->executable, '-l', $path]);
    }
}

?>
