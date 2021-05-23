<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Generator;

final class Factory
{
    public function myFactory(Generator $generator)
    {
        return [
            'word' => $generator->word
        ];
    }
}

?>
-----
<?php

namespace Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Fixture;

use Rector\Tests\Transform\Rector\Assign\PropertyFetchToMethodCallRector\Source\Generator;

final class Factory
{
    public function myFactory(Generator $generator)
    {
        return [
            'word' => $generator->word()
        ];
    }
}

?>
