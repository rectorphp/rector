<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class Fixture
{
    public function run($tokens)
    {
        for ($i = 0, $c = count($tokens); $i < $c; ++$i) {
            if ($tokens[$i][0] === T_STRING && $tokens[$i][1] === 'fn') {
                $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                    continue;
                }
                $tokens[$i][0] = self::T_FN;
                $tokens[$i][0][1] = self::T_FN;

                $tokens[$i][0][1][4] = self::T_FN;
                $value = $tokens[$i][0][1][4];
            }
        }
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\For_\ForToForeachRector\Fixture;

class Fixture
{
    public function run($tokens)
    {
        foreach ($tokens as $i => $token) {
            if ($token[0] === T_STRING && $token[1] === 'fn') {
                $previousNonSpaceToken = $this->getPreviousNonSpaceToken($tokens, $i);
                if ($previousNonSpaceToken !== null && $previousNonSpaceToken[0] === T_OBJECT_OPERATOR) {
                    continue;
                }
                $tokens[$i][0] = self::T_FN;
                $tokens[$i][0][1] = self::T_FN;

                $tokens[$i][0][1][4] = self::T_FN;
                $value = $token[0][1][4];
            }
        }
    }
}

?>
