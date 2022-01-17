<?php

namespace Rector\Tests\Php80\Rector\FunctionLike\UnionTypesRector\Fixture;

use Ds\Set;
use Reflection;
use stdClass;

interface A
{
    /**
     * @param Set<stdClass>|Set<Reflection> $list
     */
    public function execute(Set $list) : void;
}

?>
