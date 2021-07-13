<?php

namespace Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class Fixture
{
    public function run()
    {
        $code = '<?php echo 1;';

        $tokens = token_get_all($code);
        foreach ($tokens as $token) {
            if (is_array($token)) {
                $name = token_name($token[0]);
                $text = $token[1];
            } else {
                $name = null;
                $text = $token;
            }
        }
    }
}

?>
-----
<?php

namespace Rector\Tests\Php80\Rector\FuncCall\TokenGetAllToObjectRector\Fixture;

final class Fixture
{
    public function run()
    {
        $code = '<?php echo 1;';

        $tokens = \PhpToken::tokenize($code);
        foreach ($tokens as $token) {
            $name = $token->getTokenName();
            $text = $token->text;
        }
    }
}

?>
