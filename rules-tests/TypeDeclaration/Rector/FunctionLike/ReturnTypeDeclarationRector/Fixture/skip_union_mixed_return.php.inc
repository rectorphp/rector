<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

use stdClass;

class SkipUnionMixedReturn
{
    public function get1($id) {
        if (rand(0,1)) {
            return new stdClass();
        }

        return [];
    }

    /**
     * @return stdClass|array
     */
    public function get2($id) {
        if (rand(0,1)) {
            return new stdClass();
        }

        return [];
    }

    public function get3($id)
    {
        if (rand(0,1)) {
            return [];
        }

        // do nothing
    }
}
