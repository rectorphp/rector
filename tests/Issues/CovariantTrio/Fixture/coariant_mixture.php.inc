<?php

namespace Rector\Core\Tests\Issues\CovariantTrio\Fixture;

final class CoriantMixture
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

namespace Rector\Core\Tests\Issues\CovariantTrio\Fixture;

final class CoriantMixture
{
    /**
     * @param string $token
     */
    private function parseArgument($token, array $all)
    {
        $token = (string) $token;
        reset($all);
        if (($inputArgument = isset($all[$key = key($all)]) ? $all[$key = key($all)] : null) && 'command' === $inputArgument->getName()) {
        }
    }
}

?>
