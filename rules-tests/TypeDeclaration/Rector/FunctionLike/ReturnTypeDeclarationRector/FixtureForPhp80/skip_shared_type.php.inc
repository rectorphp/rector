<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\SourceForPhp80\FirstTypeUsing;
use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\SourceForPhp80\GenericSharedInterface;
use Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\SourceForPhp80\SecondTypeUsing;

final class SkipSharedType
{
    public function get(): GenericSharedInterface
    {
        if (mt_rand(0, 1)) {
            return new FirstTypeUsing();
        }

        return new SecondTypeUsing();
    }
}
