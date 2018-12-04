<?php

use Nette\Configurator;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return Configurator::DEVELOPMENT;
    }
}

?>
-----
<?php

use Nette\Configurator;

class ClassWithExternalConstant
{
    public function getValue()
    {
        return 'development';
    }
}

?>
