<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

use Symfony\Component\Console\Input\InputDefinition;

final class CommandDefinedInConstructor
{
    private $definition;

    public function __construct()
    {
        $this->definition = new InputDefinition();
    }

    /**
     * @param array|InputDefinition $definition An array of argument and option instances or a definition instance
     */
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

use Symfony\Component\Console\Input\InputDefinition;

final class CommandDefinedInConstructor
{
    /**
     * @var \Symfony\Component\Console\Input\InputDefinition
     */
    private $definition;

    public function __construct()
    {
        $this->definition = new InputDefinition();
    }

    /**
     * @param array|InputDefinition $definition An array of argument and option instances or a definition instance
     */
    public function setDefinition($definition)
    {
        if ($definition instanceof InputDefinition) {
            $this->definition = $definition;
        } else {
            $this->definition->setDefinition($definition);
        }
    }
}

?>
