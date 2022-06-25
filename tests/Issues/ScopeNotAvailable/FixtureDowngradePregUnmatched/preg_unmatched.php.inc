<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

function test()
{
        $match = ((\PREG_PATTERN_ORDER | \PREG_SET_ORDER) & $flags) ? 'preg_match_all' : 'preg_match';
        $match($regexp.'u', $this->string, $matches, $flags | \PREG_UNMATCHED_AS_NULL, $offset);
}

?>
-----
<?php

namespace Rector\Core\Tests\Issues\ScopeNotAvailable\Fixture;

function test()
{
        $match = ((\PREG_PATTERN_ORDER | \PREG_SET_ORDER) & $flags) ? 'preg_match_all' : 'preg_match';
        $match($regexp.'u', $this->string, $matches, $flags, $offset);
        array_walk_recursive($matches, function (&$value) {
            if ($value === '') {
                $value = null;
            }
        });
}

?>
