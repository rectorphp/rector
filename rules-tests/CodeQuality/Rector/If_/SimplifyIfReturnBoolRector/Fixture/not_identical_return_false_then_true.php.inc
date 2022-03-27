<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpCsFixer\Tokenizer\Token;

class NotIdenticalReturnFalseThenTrue
{
    public function run()
    {
        $docToken = new Token([]);
        if (strpos($docToken->getContent(), "\n") !== false) {
            return false;
        }

        return true;
    }
}

?>
-----
<?php

namespace Rector\Tests\CodeQuality\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpCsFixer\Tokenizer\Token;

class NotIdenticalReturnFalseThenTrue
{
    public function run()
    {
        $docToken = new Token([]);
        return strpos($docToken->getContent(), "\n") === false;
    }
}

?>
