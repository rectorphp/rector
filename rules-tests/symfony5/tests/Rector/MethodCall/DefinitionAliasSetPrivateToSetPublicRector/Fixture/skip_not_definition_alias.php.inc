<?php

namespace Rector\Symfony5\Tests\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

class SkipNotDefinitionAlias
{
    public function run()
    {
        $d = new \DateTime('now');
        $d->format('Y-m-d');
    }
}
