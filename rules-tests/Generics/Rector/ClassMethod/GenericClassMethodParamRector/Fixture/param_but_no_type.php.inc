<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Fixture;

use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\SomeMapperInterface;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\ValueObject\SpecificSearch;

final class ParamButNoType implements SomeMapperInterface
{
    private function getParams($someSearch)
    {
    }
}

?>
-----
<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Fixture;

use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\SomeMapperInterface;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\ValueObject\SpecificSearch;

final class ParamButNoType implements SomeMapperInterface
{
    public function getParams(\Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\Contract\GenericSearchInterface $someSearch)
    {
    }
}

?>
