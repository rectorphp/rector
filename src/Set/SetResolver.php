<?php

declare(strict_types=1);

namespace Rector\Core\Set;

use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Set\SetProvider;
use Rector\Set\ValueObject\Set;
use Symfony\Component\Console\Input\InputInterface;
use Symplify\SmartFileSystem\SmartFileInfo;

final class SetResolver
{
    /**
     * @var SetProvider
     */
    private $setProvider;

    public function __construct()
    {
        $this->setProvider = new SetProvider();
    }

    public function resolveSetFromInput(InputInterface $input): ?Set
    {
        $setOption = $input->getParameterOption(['-s', '--setOption']);
        if ($setOption === false) {
            return null;
        }

        return $this->setProvider->provideFilePathByName($setOption);
    }

    public function resolveSetByName(string $name): ?Set
    {
        return $this->setProvider->provideFilePathByName($name);
    }

    public function resolveSetFileInfoByName(string $name): SmartFileInfo
    {
        $set = $this->setProvider->provideFilePathByName($name);
        if ($set === null) {
            throw new ShouldNotHappenException();
        }

        return $set->getFileInfo();
    }
}
