<?php

namespace Rector\Tests\Rector\Function_\FunctionReplaceRector\Wrong;

class SomeClass
{
    protected function configure(): void
    {
        $this->setDescription(sprintf(
            'Splits monorepo packages to standalone repositories as defined in "%s" section of "%s" config.',
            'parameters > directories_to_repositories',
            'monorepo-builder.yml'
        ));

        $this->setName(sprINTf('One %s', 'two'));
    }
}
