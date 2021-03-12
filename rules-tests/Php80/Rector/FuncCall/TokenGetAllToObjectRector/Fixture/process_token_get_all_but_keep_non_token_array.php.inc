<?php

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

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

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class ProcessTokenGetAllButKeepNonTokenArray
{
    public function run()
    {
        $code = '<?php echo 1;';

        $stringTokens = \PhpToken::getAll($code);

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
