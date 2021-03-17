<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpCsFixer\Tokenizer\Tokens;

final class ReturnTokens
{
    public function createTokens()
    {
        return new Tokens();
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayReturnDocTypeRector\Fixture;

use PhpCsFixer\Tokenizer\Tokens;

final class ReturnTokens
{
    /**
     * @return \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token>
     */
    public function createTokens()
    {
        return new Tokens();
    }
}

?>
