<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpCsFixer\Tokenizer\Token;

class SomeClass
{
    public function first()
    {
        $docToken = new Token([]);
        if (strpos($docToken->getContent(), "\n") === false) {
            return true;
        }

        return false;
    }

    public function second()
    {
        $docToken = new Token([]);
        if (strpos($docToken->getContent(), "\n") !== false) {
            return true;
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\CodeQuality\Tests\Rector\If_\SimplifyIfReturnBoolRector\Fixture;

use PhpCsFixer\Tokenizer\Token;

class SomeClass
{
    public function first()
    {
        $docToken = new Token([]);
        return strpos($docToken->getContent(), "\n") === false;
    }

    public function second()
    {
        $docToken = new Token([]);
        return strpos($docToken->getContent(), "\n") !== false;
    }
}

?>
