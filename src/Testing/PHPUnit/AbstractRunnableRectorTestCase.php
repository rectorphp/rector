<?php

declare(strict_types=1);

namespace Rector\Core\Testing\PHPUnit;

use Nette\Utils\Strings;
use Rector\Core\Testing\ValueObject\SplitLine;
use ReflectionClass;
use Symplify\SmartFileSystem\SmartFileInfo;

abstract class AbstractRunnableRectorTestCase extends AbstractRectorTestCase
{
    protected function assertOriginalAndFixedFileResultEquals(string $file): void
    {
        /**
         * Todo: Duplicate from
         *
         * @see FixtureSplitter::splitContentToOriginalFileAndExpectedFile
         * ==> refactor in a method
         */
        $smartFileInfo = new SmartFileInfo($file);
        if (Strings::match($smartFileInfo->getContents(), SplitLine::SPLIT_LINE)) {
            [$originalContent, $expectedContent] = Strings::split($smartFileInfo->getContents(), SplitLine::SPLIT_LINE);
        } else {
            $originalContent = $smartFileInfo->getContents();
            $expectedContent = $originalContent;
        }

        $originalInstance = $this->loadClass($originalContent);
        if ($originalInstance !== null) {
            $expectedInstance = $this->loadClass($expectedContent);
            if ($expectedInstance !== null) {
                $actual = $originalInstance->run();
                $expected = $expectedInstance->run();

                $this->assertSame($actual, $expected);
            }
        }
    }

    protected function getTemporaryClassName(): string
    {
        $testName = (new ReflectionClass(static::class))->getShortName();
        // Todo - pull in Ramsey UUID to generate temporay class names?
//        $uuid      = Uuid::uuid4()->toString();
        $uuid = md5((string) random_int(0, 100000000));
        $className = $testName . '_' . $uuid;

        return Strings::replace($className, '#[^0-9a-zA-Z]#', '_');
    }

    protected function loadClass(string $classContent): ?RunnableInterface
    {
        $className = $this->getTemporaryClassName();
        $loadable = Strings::replace($classContent, '#\\s*<\\?php#', '');
        $loadable = Strings::replace($loadable, '#\\s*namespace.*;#', '');
        $loadable = Strings::replace($loadable, '#class\\s+(\\S*)\\s+#', sprintf('class %s ', $className));
        eval($loadable);
        if (is_a($className, RunnableInterface::class, true)) {
            /**
             * @var RunnableInterface
             */
            return new $className();
        }
        return null;
    }
}
