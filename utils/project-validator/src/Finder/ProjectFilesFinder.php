<?php

declare(strict_types=1);

namespace Rector\Utils\ProjectValidator\Finder;

use Symplify\SmartFileSystem\Finder\SmartFinder;
use Symplify\SmartFileSystem\SmartFileInfo;

final class ProjectFilesFinder
{
    /**
     * @var SmartFinder
     */
    private $smartFinder;

    public function __construct(SmartFinder $smartFinder)
    {
        $this->smartFinder = $smartFinder;
    }

    /**
     * @return SmartFileInfo[]
     */
    public function find(): array
    {
        return $this->smartFinder->find([
            __DIR__ . '/../../../../packages',
            __DIR__ . '/../../../../rules',
            __DIR__ . '/../../../../src',
            __DIR__ . '/../../../../tests',
        ], '*');
    }
}
