<?php

namespace Rector\Core\Tests\Issues\Fixture;

final class ArgvInput
{
    private function parseArgument(string $token, array $all)
    {
        if (($inputArgument = $all[$key = array_key_first($all)] ?? null) && 'command' === $inputArgument->getName()) {
        }
    }
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\Fixture;

final class ArgvInput
{
    private function parseArgument(string $token, array $all)
    {
        reset($all);
        if (($inputArgument = isset($all[$key = key($all)]) ? $all[$key = key($all)] : null) && 'command' === $inputArgument->getName()) {
        }
    }
}

?>
