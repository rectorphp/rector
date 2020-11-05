<?php

namespace Nette\DI\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class DefinitionSchema
{
    public static function getServiceSchema(): Schema
    {
        return Expect::structure([
            'factory' => Expect::type('Prefix_\\callable|Nette\\DI\\Definitions\\Statement'),
            'setup' => Expect::listOf('Prefix_\\callable|Nette\\DI\\Definitions\\Statement|array:1'),
        ]);
    }
}

?>
-----
<?php

namespace Nette\DI\Config;

use Nette\Schema\Expect;
use Nette\Schema\Schema;

class DefinitionSchema
{
    public static function getServiceSchema(): Schema
    {
        return Expect::structure([
            'factory' => Expect::type('callable|Prefix_\\Nette\\DI\\Definitions\\Statement'),
            'setup' => Expect::listOf('callable|Prefix_\\Nette\\DI\\Definitions\\Statement|array:1'),
        ]);
    }
}

?>
