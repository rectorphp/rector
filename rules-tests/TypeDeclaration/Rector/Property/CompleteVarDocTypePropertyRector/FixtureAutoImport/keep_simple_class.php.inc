<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\FixtureAutoImport;

use stdClass;

final class KeepSimpleClass
{
    /**
     * @var stdClass|null
     */
    public $obj;

    public function run()
    {
        $this->obj = new stdClass();
    }
}
