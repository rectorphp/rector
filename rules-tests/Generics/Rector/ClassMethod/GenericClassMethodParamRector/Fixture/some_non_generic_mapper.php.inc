<?php

declare(strict_types=1);

namespace Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Fixture;

use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\SomeMapperInterface;
use Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\ValueObject\SpecificSearch;

final class SomeNonGenericMapper implements SomeMapperInterface
{
    private function getParams(SpecificSearch $search)
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

final class SomeNonGenericMapper implements SomeMapperInterface
{
    /**
     * @param \Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\ValueObject\SpecificSearch $search
     */
    public function getParams(\Rector\Tests\Generics\Rector\ClassMethod\GenericClassMethodParamRector\Source\Contract\GenericSearchInterface $search)
    {
    }
}

?>
