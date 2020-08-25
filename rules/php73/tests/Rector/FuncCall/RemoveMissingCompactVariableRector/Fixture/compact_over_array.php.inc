<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class CompactOverArray
{
    private function test_argument_without_existing_vars_will_be_removed()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo', 'bar'], ['baz']);
    }

    private function test_argument_missing_vars_will_be_removed_from_array_argument()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo'], ['bar', 'baz']);
    }

    private function test_works_as_expected_for_array_and_string_mixed_arguments_call()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo'], ['bar'], 'baz');
    }
}

?>
-----
<?php

namespace Rector\Php73\Tests\Rector\FuncCall\RemoveMissingCompactVariableRector\Fixture;

class CompactOverArray
{
    private function test_argument_without_existing_vars_will_be_removed()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo', 'bar']);
    }

    private function test_argument_missing_vars_will_be_removed_from_array_argument()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo'], ['bar']);
    }

    private function test_works_as_expected_for_array_and_string_mixed_arguments_call()
    {
        $foo = 'foo';
        $bar = 'bar';

        compact(['foo'], ['bar']);
    }
}

?>
