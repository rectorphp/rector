<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector\Fixture;

use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $symfonyStyle->info(sprintf('Hi %s', 'Tom'));
    }
}

?>
-----
<?php

namespace Rector\CodingStyle\Tests\Rector\MethodCall\UseMessageVariableForSprintfInSymfonyStyleRector\Fixture;

use Symfony\Component\Console\Style\SymfonyStyle;

final class SomeClass
{
    public function run(SymfonyStyle $symfonyStyle)
    {
        $message = sprintf('Hi %s', 'Tom');
        $symfonyStyle->info($message);
    }
}

?>
