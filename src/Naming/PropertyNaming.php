<?php declare(strict_types=1);

namespace Rector\Naming;

use Nette\Utils\Strings;

final class PropertyNaming
{
    public function fqnToVariableName(string $fqn): string
    {
        return lcfirst($this->fqnToShortName($fqn));
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

        $lastNamePart = Strings::after($fqn, '\\', - 1);
        if (Strings::endsWith($lastNamePart, 'Interface')) {
            return Strings::substring($lastNamePart, 0, - strlen('Interface'));
        }

        return $lastNamePart;
    }
}
