<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => [
                'Twig_Node' => [
                    'getLine' => 'getTemplateLine',
                    'getFilename' => 'getTemplateName',
                ],
                'Twig_Template' => [
                    'getSource' => 'getSourceContext',
                ],
        ]]]);
}

?>
-----
<?php

namespace Rector\SymfonyPhpConfig\Tests\Rector\MethodCall\ReplaceArrayWithObjectRector\Fixture;

use Rector\Renaming\Rector\MethodCall\RenameMethodRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $services = $containerConfigurator->services();

    $services->set(RenameMethodRector::class)
        ->call('configure', [[
            RenameMethodRector::OLD_TO_NEW_METHODS_BY_CLASS => \Rector\SymfonyPhpConfig\inline_value_objects([new \Rector\Renaming\ValueObject\MethodCallRename('Twig_Node', 'getLine', 'getTemplateLine'), new \Rector\Renaming\ValueObject\MethodCallRename('Twig_Node', 'getFilename', 'getTemplateName'), new \Rector\Renaming\ValueObject\MethodCallRename('Twig_Template', 'getSource', 'getSourceContext')])]]);
}

?>
