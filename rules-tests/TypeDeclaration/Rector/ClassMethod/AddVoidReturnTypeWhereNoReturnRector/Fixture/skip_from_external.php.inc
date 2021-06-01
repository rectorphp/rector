<?php

namespace Rector\Tests\TypeDeclaration\Rector\ClassMethod\AddVoidReturnTypeWhereNoReturnRector\Fixture;

use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class SkipFromExternal extends PhpFileLoader
{
    public function load($resource, string $type = null)
    {
    }
}

?>
