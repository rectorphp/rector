<?php

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class CodeSnifferCase
{
    public function run()
    {
        $code = '<?php echo 1;';

        $stringTokens = token_get_all($code);
        foreach ($stringTokens as $token) {
            if (is_array($token) === true && $token[0] === T_VARIABLE) {
                $error = 'Variable "%s" not allowed in double quoted string; use concatenation instead';
                $data  = [$token[1]];
                return $data;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class CodeSnifferCase
{
    public function run()
    {
        $code = '<?php echo 1;';

        $stringTokens = \PhpToken::getAll($code);
        foreach ($stringTokens as $token) {
            if ($token->is(T_VARIABLE)) {
                $error = 'Variable "%s" not allowed in double quoted string; use concatenation instead';
                $data  = [$token->text];
                return $data;
            }
        }
    }
}

?>
