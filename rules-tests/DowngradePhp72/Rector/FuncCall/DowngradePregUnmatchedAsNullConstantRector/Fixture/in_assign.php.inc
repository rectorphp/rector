<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

function inAssign($pattern, $subject, $matches, $flags, $offset)
{
    $result = preg_match($pattern, $subject, $matches, $flags | PREG_UNMATCHED_AS_NULL, $offset);
    if ($result === false) {
        throw \Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
    }
}

?>
-----
<?php

namespace Rector\Tests\DowngradePhp72\Rector\FuncCall\DowngradePregUnmatchedAsNullConstantRector\Fixture;

function inAssign($pattern, $subject, $matches, $flags, $offset)
{
    preg_match($pattern, $subject, $matches, $flags, $offset);
    array_walk_recursive($matches, function (&$value) {
        if ($value === '') {
            $value = null;
        }
    });
    $result = $matches === [] ? false : 1;
    if ($result === false) {
        throw \Composer\Pcre\PcreException::fromFunction('preg_match', $pattern);
    }
}

?>
