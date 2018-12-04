<?php

function unsetAndIsset()
{
    $container = new Nette\DI\Container;
    isset($container['someService']);
    unset($container['someService']);
}

?>
-----
<?php

function unsetAndIsset()
{
    $container = new Nette\DI\Container;
    $container->hasService('someService');
    $container->removeService('someService');
}

?>
