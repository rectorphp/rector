<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class ConstantInsideArray
{
    public function run()
    {
        $replacements = [
            \PHPUnit\Framework\TestCase\Notice::class
        ];

        var_dump($replacements);
    }
}

?>
-----
<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

class ConstantInsideArray
{
    /**
     * @var string[]
     */
    private const REPLACEMENTS = [
        \PHPUnit\Framework\TestCase\Notice::class
    ];
    public function run()
    {
        var_dump(self::REPLACEMENTS);
    }
}

?>