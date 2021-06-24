<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use Rector\BetterPhpDocParser\ValueObject\Parser\BetterTokenIterator;

final class PrivatePropertyReflection
{
    public function getTokens(BetterTokenIterator $betterTokenIterator): array
    {
        return $betterTokenIterator->getTokens();
    }
}
