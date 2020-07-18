<?php

use Rector\Core\Configuration\Option;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $parameters = $containerConfigurator->parameters();

    $parameters->set(Option::RECTOR_RECIPE, [
        # run "bin/rector create" to create a new Rector + tests from this config
        'package' => 'Symfony',
        'name' => 'RemoveDefaultGetBlockPrefixRector',
        'node_types' => ['ClassMethod'],
        'description' => 'Rename `getBlockPrefix()` if it returns the default value - class to underscore, e.g. UserFormType = user_form',
        'code_before' => '<?php
use Symfony\Component\Form\AbstractType;
class TaskType extends AbstractType
{
    public function getBlockPrefix()
    {
        return \'task\';
    }
}
',
        'code_after' => '<?php
use Symfony\Component\Form\AbstractType;
class TaskType extends AbstractType
{
}
',
        'source' => [
            # e.g. link to RFC or headline in upgrade guide, 1 or more in the list
            'https://github.com/symfony/symfony/blob/3.4/UPGRADE-3.0.md',
        ],
        # e.g. symfony30, target config to append this rector to
        'set' => 'symfony30',
    ]);
};
