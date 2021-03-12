<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpCsFixer\Doctrine\Annotation\Tokens;

final class TokenPhpCsFixerDoctrine
{
    public function __construct(Tokens $tokens)
    {
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddArrayParamDocTypeRector\Fixture;

use PhpCsFixer\Doctrine\Annotation\Tokens;

final class TokenPhpCsFixerDoctrine
{
    /**
     * @param \PhpCsFixer\Doctrine\Annotation\Tokens<\PhpCsFixer\Doctrine\Annotation\Token> $tokens
     */
    public function __construct(Tokens $tokens)
    {
    }
}

?>
