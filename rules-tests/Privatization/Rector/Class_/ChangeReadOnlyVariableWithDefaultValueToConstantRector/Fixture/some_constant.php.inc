<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SomeConstant
{
    public function run()
    {
        $replacements = \PHPUnit\Framework\TestCase\Notice::class;

        if ($replacements === 'asdf') {
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class SomeConstant
{
    /**
     * @var string
     */
    private const REPLACEMENTS = \PHPUnit\Framework\TestCase\Notice::class;
    public function run()
    {
        if (self::REPLACEMENTS === 'asdf') {
        }
    }
}

?>
