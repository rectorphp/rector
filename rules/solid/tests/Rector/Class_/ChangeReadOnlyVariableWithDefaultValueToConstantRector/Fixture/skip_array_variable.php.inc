<?php

namespace Rector\SOLID\Tests\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

use Rector\Renaming\Rector\Name\RenameClassRector;
use Symfony\Component\Yaml\Yaml;

class SkipArrayVariable
{
    private function createRectorYamlContent(): string
    {
        $oldToNewClasses = $this->renamedClassesCollector->getOldToNewClassesSortedByHighestParentsAsString();

        $data = [
            # rector.yaml
            'services' => [
                RenameClassRector::class => [
                    '$oldToNewClasses' => $oldToNewClasses,
                ],
            ],
        ];

        return Yaml::dump($data, 10);
    }
}
