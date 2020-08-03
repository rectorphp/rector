<?php

declare(strict_types=1);

namespace Rector\Set;

use Rector\Core\Util\StaticRectorStrings;
use Rector\Set\ValueObject\SetList;
use ReflectionClass;
use Symplify\SetConfigResolver\Provider\AbstractSetProvider;
use Symplify\SetConfigResolver\ValueObject\Set;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetProvider extends AbstractSetProvider
{
    /**
     * @var Set[]
     */
    private $sets = [];

    public function __construct()
    {
        $setListReflectionClass = new ReflectionClass(SetList::class);

        // new kind of paths sets
        foreach ($setListReflectionClass->getConstants() as $name => $setPath) {
            if (! file_exists($setPath)) {
                continue;
            }

            $setName = StaticRectorStrings::constantToDashes($name);
            $this->sets[] = new Set($setName, new SmartFileInfo($setPath));
        }
    }

    /**
     * @return Set[]
     */
    public function provide(): array
    {
        return $this->sets;
    }

    public function provideByName(string $setName): ?Set
    {
        $parentSet = parent::provideByName($setName);
        if ($parentSet instanceof Set) {
            return $parentSet;
        }

        // sencond approach by set path
        foreach ($this->sets as $set) {
            if (realpath($set->getSetFileInfo()->getRealPath()) !== realpath($setName)) {
                continue;
            }

            return $set;
        }

        return null;
    }
}
