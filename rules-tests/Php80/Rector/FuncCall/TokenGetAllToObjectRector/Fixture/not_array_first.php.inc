<?php

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class NotArrayFirst
{
    public function run()
    {
        $code = '<?php echo 1;';

        $tokens = token_get_all($code);
        foreach ($tokens as $token) {
            if (! is_array($token)) {
                $name = null;
                $text = $token;
            } else {
                $name = token_name($token[0]);
                $text = $token[1];

            }
        }
    }
}

?>
-----
<?php

namespace Rector\Php80\Tests\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class NotArrayFirst
{
    public function run()
    {
        $code = '<?php echo 1;';

        $tokens = \PhpToken::getAll($code);
        foreach ($tokens as $token) {
            $name = $token->getTokenName();
            $text = $token->text;
        }
    }
}

?>
