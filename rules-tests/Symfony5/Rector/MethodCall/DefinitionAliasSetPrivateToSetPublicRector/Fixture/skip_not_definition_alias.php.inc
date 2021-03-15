<?php

namespace Rector\Tests\Symfony5\Rector\MethodCall\DefinitionAliasSetPrivateToSetPublicRector\Fixture;

class SkipNotDefinitionAlias
{
    public function run()
    {
        $d = new \DateTime('now');
        $d->format('Y-m-d');
    }
}
