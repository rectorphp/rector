<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Fixture;

use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\SomeMapperInterface;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\ValueObject\SpecificSearch;

final class NoParam implements SomeMapperInterface
{
    private function getParams()
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

final class NoParam implements SomeMapperInterface
{
    public function getParams(\Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\Contract\GenericSearchInterface $genericSearch)
    {
    }
}

?>
