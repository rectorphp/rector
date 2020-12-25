<?php

declare(strict_types=1);

namespace Rector\Set;

use Nette\Utils\Strings;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Util\StaticRectorStrings;
use Rector\Set\ValueObject\DowngradeSetList;
use Rector\Set\ValueObject\SetList;
use ReflectionClass;
use Symplify\SetConfigResolver\Exception\SetNotFoundException;
use Symplify\SetConfigResolver\Provider\AbstractSetProvider;
use Symplify\SetConfigResolver\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;

final class RectorSetProvider extends AbstractSetProvider
{
    /**
     * @var string
     * @see https://regex101.com/r/8gO8w6/1
     */
    private const DASH_NUMBER_REGEX = '#\-(\d+)#';

    /**
     * @var string[]
     */
    private const SET_LIST_CLASSES = [SetList::class, DowngradeSetList::class];

    /**
     * @var Set[]
     */
    private $sets = [];

    public function __construct()
    {
        foreach (self::SET_LIST_CLASSES as $setListClass) {
            $setListReflectionClass = new ReflectionClass($setListClass);
            $this->hydrateSetsFromConstants($setListReflectionClass);
        }
    }

    /**
     * @return Set[]
     */
    public function provide(): array
    {
        return $this->sets;
    }

    public function provideByName(string $desiredSetName): ?Set
    {
        $foundSet = parent::provideByName($desiredSetName);
        if ($foundSet instanceof Set) {
            return $foundSet;
        }

        // sencond approach by set path
        foreach ($this->sets as $set) {
            if (! file_exists($desiredSetName)) {
                continue;
            }

            $desiredSetFileInfo = new SmartFileInfo($desiredSetName);
            $setFileInfo = $set->getSetFileInfo();
            if ($setFileInfo->getRealPath() !== $desiredSetFileInfo->getRealPath()) {
                continue;
            }

            return $set;
        }

        $message = sprintf('Set "%s" was not found', $desiredSetName);
        throw new SetNotFoundException($message, $desiredSetName, $this->provideSetNames());
    }

    private function hydrateSetsFromConstants(ReflectionClass $setListReflectionClass): void
    {
        foreach ($setListReflectionClass->getConstants() as $name => $setPath) {
            if (! file_exists($setPath)) {
                $message = sprintf('Set path "%s" was not found', $name);
                throw new ShouldNotHappenException($message);
            }

            $setName = StaticRectorStrings::constantToDashes($name);

            // remove `-` before numbers
            $setName = Strings::replace($setName, self::DASH_NUMBER_REGEX, '$1');
            $this->sets[] = new Set($setName, new SmartFileInfo($setPath));
        }
    }
}
