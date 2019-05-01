<?php declare(strict_types=1);

namespace Rector\CodingStyle\Imports;

use Rector\CodingStyle\Naming\ClassNaming;

final class ImportsInClassCollection
{
    /**
     * @var string[]
     */
    private $importsInClass = [];

    /**
     * @var ClassNaming
     */
    private $classNaming;

    public function __construct(ClassNaming $classNaming)
    {
        $this->classNaming = $classNaming;
    }

    public function addImport(string $import): void
    {
        $this->importsInClass[] = $import;
    }

    public function hasImport(string $import): bool
    {
        return in_array($import, $this->importsInClass, true);
    }

    public function canImportBeAdded(string $import): bool
    {
        $shortImport = $this->classNaming->getShortName($import);

        foreach ($this->importsInClass as $importsInClass) {
            $shortImportInClass = $this->classNaming->getShortName($importsInClass);
            if ($importsInClass !== $import && $shortImportInClass === $shortImport) {
                return true;
            }
        }

        return false;
    }

    public function reset(): void
    {
        $this->importsInClass = [];
    }

    /**
     * @return string[]
     */
    public function get(): array
    {
        return $this->importsInClass;
    }
}
