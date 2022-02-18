<?php

namespace Rector\Tests\Privatization\Rector\Class_\ChangeReadOnlyVariableWithDefaultValueToConstantRector\Fixture;

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
                    $oldToNewClasses,
                ],
            ],
        ];

        return Yaml::dump($data, 10);
    }
}
