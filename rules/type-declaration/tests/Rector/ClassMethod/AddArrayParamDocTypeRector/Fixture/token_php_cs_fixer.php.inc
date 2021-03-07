<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpCsFixer\Tokenizer\Tokens;

final class TokenPhpCsFixer
{
    public function __construct(Tokens $tokens)
    {
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpCsFixer\Tokenizer\Tokens;

final class TokenPhpCsFixer
{
    /**
     * @param \PhpCsFixer\Tokenizer\Tokens<\PhpCsFixer\Tokenizer\Token> $tokens
     */
    public function __construct(Tokens $tokens)
    {
    }
}

?>
