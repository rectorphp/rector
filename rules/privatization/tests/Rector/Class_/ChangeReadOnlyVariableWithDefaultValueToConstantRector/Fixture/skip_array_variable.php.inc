<?php

namespace Rector\Privatization\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\Yaml\Yaml;

class SkipArrayVariable
{
    private function createRectorYamlContent(): string
    {
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClassesSortedByHighestParentsAsString();

        $data = [
            'services' => [
                RenameClassRector::class => [
                    RenameClassRector::OLD_TO_NEW_CLASSES => $oldToNewClasses,
                ],
            ],
        ];

        return Yaml::dump($data, 10);
    }
}
