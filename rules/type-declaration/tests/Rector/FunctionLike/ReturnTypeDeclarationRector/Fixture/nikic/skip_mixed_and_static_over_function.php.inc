<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture\nikic;

class SkipMixedAndStaticOverFunction
{
    public function run(): void
    {
        /** @return mixed */
        function test222($value) {
            return $value;
        }

        /** @return static */
        function test333($value) {
            return $value;
        }
    }
}
