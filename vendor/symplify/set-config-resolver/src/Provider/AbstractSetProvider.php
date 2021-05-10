<?php

declare (strict_types=1);
namespace RectorPrefix20210510\Symplify\SetConfigResolver\Provider;

use RectorPrefix20210510\Nette\Utils\Strings;
use RectorPrefix20210510\Symplify\SetConfigResolver\Contract\SetProviderInterface;
use RectorPrefix20210510\Symplify\SetConfigResolver\Exception\SetNotFoundException;
use RectorPrefix20210510\Symplify\SetConfigResolver\ValueObject\Set;
use RectorPrefix20210510\Symplify\SymplifyKernel\Exception\ShouldNotHappenException;
abstract class AbstractSetProvider implements SetProviderInterface
{
    /**
     * @return string[]
     */
    public function provideSetNames() : array
    {
        $setNames = [];
        $sets = $this->provide();
        foreach ($sets as $set) {
            $setNames[] = $set->getName();
        }
        return $setNames;
    }
    public function provideByName(string $desiredSetName) : ?Set
    {
        // 1. name-based approach
        $sets = $this->provide();
        foreach ($sets as $set) {
            if ($set->getName() !== $desiredSetName) {
                continue;
            }
            return $set;
        }
        // 2. path-based approach
        try {
            $sets = $this->provide();
            foreach ($sets as $set) {
                // possible bug for PHAR files, see https://bugs.php.net/bug.php?id=52769
                // this is very tricky to handle, see https://stackoverflow.com/questions/27838025/how-to-get-a-phar-file-real-directory-within-the-phar-file-code
                $setUniqueId = $this->resolveSetUniquePathId($set->getSetPathname());
                $desiredSetUniqueId = $this->resolveSetUniquePathId($desiredSetName);
                if ($setUniqueId !== $desiredSetUniqueId) {
                    continue;
                }
                return $set;
            }
        } catch (ShouldNotHappenException $shouldNotHappenException) {
        }
        $message = \sprintf('Set "%s" was not found', $desiredSetName);
        throw new SetNotFoundException($message, $desiredSetName, $this->provideSetNames());
    }
    private function resolveSetUniquePathId(string $setPath) : string
    {
        $setPath = Strings::after($setPath, \DIRECTORY_SEPARATOR, -2);
        if ($setPath === null) {
            throw new ShouldNotHappenException();
        }
        return $setPath;
    }
}
