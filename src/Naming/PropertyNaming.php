<?php declare(strict_types=1);

namespace Rector\Naming;

use Nette\Utils\Strings;
use PHPStan\Type\ObjectType;

final class PropertyNaming
{
    public function fqnToVariableName(ObjectType $objectType): string
    {
        return lcfirst($this->fqnToShortName($objectType->getClassName()));
    }

    /**
     * @source https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName): string
    {
        $camelCaseName = str_replace('_', '', ucwords($underscoreName, '_'));

        return lcfirst($camelCaseName);
    }

    private function fqnToShortName(string $fqn): string
    {
        if (! Strings::contains($fqn, '\\')) {
            return $fqn;
        }

        /** @var string $lastNamePart */
        $lastNamePart = Strings::after($fqn, '\\', - 1);
        if (Strings::endsWith($lastNamePart, 'Interface')) {
            return Strings::substring($lastNamePart, 0, - strlen('Interface'));
        }

        return $lastNamePart;
    }
}
