<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionRector\Fixture;

class Fixture2
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

?>
-----
<?php

namespace Rector\Renaming\Tests\Rector\FuncCall\RenameFunctionRector\Fixture;

class Fixture2
{
    protected function configure(): void
    {
        $this->setDescription(\Safe\sprintf(
            'Splits monorepo packages to standalone repositories as defined in "%s" section of "%s" config.',
            'parameters > directories_to_repositories',
            'monorepo-builder.yml'
        ));

        $this->setName(\Safe\sprintf('One %s', 'two'));
    }
}

?>
