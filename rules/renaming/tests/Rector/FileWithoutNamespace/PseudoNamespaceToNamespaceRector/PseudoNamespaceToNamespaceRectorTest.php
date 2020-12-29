<?php

declare(strict_types=1);

namespace Rector\Renaming\Tests\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;

use Iterator;
use PHPUnit\Framework\ExpectationFailedException;
use PHPUnit\Framework\TestCase;
use Rector\Generic\ValueObject\PseudoNamespaceToNamespace;
use Rector\Renaming\Rector\FileWithoutNamespace\PseudoNamespaceToNamespaceRector;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use Symplify\SmartFileSystem\SmartFileInfo;

final class PseudoNamespaceToNamespaceRectorTest extends AbstractRectorTestCase
{
    /**
     * @dataProvider provideData()
     */
    public function test(SmartFileInfo $fileInfo): void
    {
        $autoloadClassNameAssertion = $this->getAutoloadClassNameAssertion();

        $this->doTestFileInfo($fileInfo);

        $autoloadClassNameAssertion->assert($fileInfo);
    }

    public function provideData(): Iterator
    {
        return $this->yieldFilesFromDirectory(__DIR__ . '/Fixture');
    }

    /**
     * @return array<string, mixed[]>
     */
    protected function getRectorsWithConfiguration(): array
    {
        return [
            PseudoNamespaceToNamespaceRector::class => [
                PseudoNamespaceToNamespaceRector::NAMESPACE_PREFIXES_WITH_EXCLUDED_CLASSES => [
                    new PseudoNamespaceToNamespace('PHPUnit_', ['PHPUnit_Framework_MockObject_MockObject']),
                    new PseudoNamespaceToNamespace('ChangeMe_', ['KeepMe_']),
                    new PseudoNamespaceToNamespace(
                        'Rector_Renaming_Tests_Rector_FileWithoutNamespace_PseudoNamespaceToNamespaceRector_Fixture_'
                    ),
                ],
            ],
        ];
    }

    /** @noinspection PhpMissingReturnTypeInspection */
    // @phpstan-ignore-next-line
    private function getAutoloadClassNameAssertion()
    {
        return new class ($this) { // @phpstan-ignore-line
            // ref: https://www.php.net/manual/en/language.oop5.basic.php
            private const CLASS_NAME = '/^
                # valid PHP class-name (incl. optional leading \\ name-space separator)
                \\\\?
                [a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*
                (?: \\\\ [a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]* )*
                $/Dx';

            /**
             * @var TestCase
             */
            private $testCase;

            /**
             * @var string[]
             */
            private $sequence = [];

            /**
             * @param TestCase $testCase
             * @noinspection InterfacesAsConstructorDependenciesInspection
             */
            public function __construct(TestCase $testCase)
            {
                $this->testCase = $testCase;
                \spl_autoload_register($this, true ,true);
            }

            public function __invoke(string $className): void
            {
                $this->sequence[] = $className;
            }

            /**
             * @param SmartFileInfo $fileInfo
             * @throws ExpectationFailedException
             */
            public function assert(SmartFileInfo $fileInfo): void
            {
                foreach ($this->sequence as $className) {
                    $this->testCase::assertMatchesRegularExpression(
                        self::CLASS_NAME,
                        $className,
                        $fileInfo->getBasename() . ' class-name validity'
                    );
                }
                $this->sequence = [];
            }

            public function __destruct()
            {
                \spl_autoload_unregister($this);
            }
        };
    }
}
