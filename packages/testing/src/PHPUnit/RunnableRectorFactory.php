<?php

declare(strict_types=1);

namespace Rector\Testing\PHPUnit;

use Nette\Utils\Random;
use Nette\Utils\Strings;
use PhpParser\NodeFinder;
use Rector\Testing\Contract\RunnableInterface;
use Rector\Testing\PHPUnit\Runnable\ClassLikeNamesSuffixer;
use Rector\Testing\PHPUnit\Runnable\RunnableClassFinder;
use Symplify\SmartFileSystem\SmartFileInfo;
use Symplify\SmartFileSystem\SmartFileSystem;

final class RunnableRectorFactory
{
    /**
     * @var RunnableClassFinder
     */
    private $runnableClassFinder;

    /**
     * @var ClassLikeNamesSuffixer
     */
    private $classLikeNamesSuffixer;

    /**
     * @var SmartFileSystem
     */
    private $smartFileSystem;

    public function __construct()
    {
        $this->runnableClassFinder = new RunnableClassFinder(new NodeFinder());
        $this->classLikeNamesSuffixer = new ClassLikeNamesSuffixer();
        $this->smartFileSystem = new SmartFileSystem();
    }

    public function createRunnableClass(SmartFileInfo $classContentFileInfo): RunnableInterface
    {
        $temporaryPath = $this->createTemporaryPathWithPrefix($classContentFileInfo);

        $contents = $classContentFileInfo->getContents();
        $temporaryClassSuffix = $this->getTemporaryClassSuffix();

        $suffixedFileContent = $this->classLikeNamesSuffixer->suffixContent($contents, $temporaryClassSuffix);

        $this->smartFileSystem->dumpFile($temporaryPath, $suffixedFileContent);
        include_once $temporaryPath;

        $runnableFullyQualifiedClassName = $this->runnableClassFinder->find($suffixedFileContent);

        return new $runnableFullyQualifiedClassName();
    }

    private function createTemporaryPathWithPrefix(SmartFileInfo $smartFileInfo): string
    {
        // warning: if this hash is too short, the file can becom "identical"; took me 1 hour to find out
        $hash = Strings::substring(md5($smartFileInfo->getRealPath()), -15);

        return sprintf(sys_get_temp_dir() . '/_rector_runnable_%s_%s', $hash, $smartFileInfo->getBasename('.inc'));
    }

    private function getTemporaryClassSuffix(): string
    {
        return Random::generate(30);
    }
}
