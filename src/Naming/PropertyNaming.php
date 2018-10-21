<?php declare(strict_types=1);

namespace Rector\Naming;

use Nette\Utils\Strings;

final class PropertyNaming
{
    public function fqnToVariableName(string $fqn): string
    {
        return lcfirst($this->fqnToShortName($fqn));
    }

    public function fqnToShortName(string $fqn): string
    {
        if (! Strings::contains($fqn, '\\')) {
            return $fqn;
        }

        $nameSpaceParts = explode('\\', $fqn);

        /** @var string $lastNamePart */
        $lastNamePart = end($nameSpaceParts);

        if (Strings::endsWith($lastNamePart, 'Interface')) {
            return Strings::substring($lastNamePart, 0, - strlen('Interface'));
        }

        return $lastNamePart;
    }

    /**
     * @source https://stackoverflow.com/a/2792045/1348344
     */
    public function underscoreToName(string $underscoreName): string
    {
        $camelCaseName = str_replace('_', '', ucwords($underscoreName, '_'));

        return lcfirst($camelCaseName);
    }
}
