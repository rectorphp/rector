<?php

namespace Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class ProcessTokenGetAllButKeepNonTokenArray
{
    public function run()
    {
        $code = '<?php echo 1;';

        $stringTokens = token_get_all($code);

        $nonToken = 'blaaa';
        foreach ($stringTokens as $token) {
            if (is_array($nonToken) === true && $nonToken[0] === T_VARIABLE) {
                $error = 'Variable "%s" not allowed in double quoted string; use concatenation instead';
                $data  = [$nonToken[1]];
                return $data;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class ProcessTokenGetAllButKeepNonTokenArray
{
    public function run()
    {
        $code = '<?php echo 1;';

        $stringTokens = \PhpToken::tokenize($code);

        $nonToken = 'blaaa';
        foreach ($stringTokens as $token) {
            if (is_array($nonToken) === true && $nonToken[0] === T_VARIABLE) {
                $error = 'Variable "%s" not allowed in double quoted string; use concatenation instead';
                $data  = [$nonToken[1]];
                return $data;
            }
        }
    }
}

?>
