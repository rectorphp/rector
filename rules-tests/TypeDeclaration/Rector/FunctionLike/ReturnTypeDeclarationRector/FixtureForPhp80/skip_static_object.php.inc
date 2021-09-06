<?php

namespace Rector\Tests\TypeDeclaration\Rector\FunctionLike\ReturnTypeDeclarationRector\FixtureForPhp80;

class SkipStaticObject
{
    /**
     * @var object
     */
    protected $obj;

    public function get($obj = null)
    {
        if (func_num_args() === 0) {
            return $this->obj;
        }

        $this->obj = $obj;
        return $this;
    }
}
