<?php

declare (strict_types=1);
namespace Rector\Testing\PHPUnit\Behavior;

use RectorPrefix202208\Nette\Utils\FileSystem;
use Rector\Core\Exception\ShouldNotHappenException;
use RectorPrefix202208\Symplify\SmartFileSystem\SmartFileInfo;
trait MultipleFilesChangedTrait
{
    protected function doTestFileInfoWithAdditionalChanges(SmartFileInfo $fixtureFileInfo, bool $allowMatches = \true) : void
    {
        $separator = '-----';
        [$originalContent, $expectedContent, $additionalInfo] = \explode($separator, $fixtureFileInfo->getContents(), 3);
        $additionalChanges = \explode($separator, $additionalInfo);
        /** @var array<array{0: ?string, 1: ?string, 2: ?string}> $additionalFileChanges */
        $additionalFileChanges = \array_chunk($additionalChanges, 3);
        $expectedFileChanges = $this->prepareAdditionalChangedFiles($additionalFileChanges);
        $fixturePath = $this->getFixtureTempDirectory() . '/' . $fixtureFileInfo->getFilename();
        $this->createFixtureDir($fixturePath);
        $fixtureContent = $originalContent;
        /** @var string $expectedContent */
        $trimmedExpectedContent = \trim($expectedContent);
        if ($trimmedExpectedContent !== '') {
            $fixtureContent .= $separator . $expectedContent;
        }
        FileSystem::write($fixturePath, $fixtureContent);
        $newFileInfo = new SmartFileInfo($fixturePath);
        $this->doTestFileInfo($newFileInfo, $allowMatches);
        $this->checkAdditionalChanges($expectedFileChanges);
        if (\file_exists($fixturePath)) {
            FileSystem::delete($fixturePath);
        }
    }
    /**
     * @param array<array{0: ?string, 1: ?string, 2: ?string}> $additionalFileChanges
     * @return array<string, string>
     */
    private function prepareAdditionalChangedFiles(array $additionalFileChanges) : array
    {
        $expectedFileChanges = [];
        foreach ($additionalFileChanges as $additionalFileChange) {
            $path = isset($additionalFileChange[0]) ? \trim($additionalFileChange[0]) : null;
            if ($path === null) {
                throw new ShouldNotHappenException('Path for additional change must be set');
            }
            $fullPath = $this->getFixtureTempDirectory() . '/' . $path;
            $input = isset($additionalFileChange[1]) ? \trim($additionalFileChange[1]) : null;
            if ($input) {
                $this->createFixtureDir($fullPath);
                FileSystem::write($fullPath, $input);
            }
            $expectedFileChanges[$fullPath] = isset($additionalFileChange[2]) ? \trim($additionalFileChange[2]) : '';
        }
        return $expectedFileChanges;
    }
    /**
     * @param array<string, string> $expectedFileChanges
     */
    private function checkAdditionalChanges(array $expectedFileChanges) : void
    {
        $addedFilesWithContent = $this->removedAndAddedFilesCollector->getAddedFilesWithContent();
        $addedFiles = [];
        foreach ($addedFilesWithContent as $addedFileWithContent) {
            [, $addedFilePathWithContentFilePath] = \explode('_temp_fixture_easy_testing', $addedFileWithContent->getFilePath());
            $addedFiles[$addedFilePathWithContentFilePath] = $addedFileWithContent;
        }
        foreach ($expectedFileChanges as $path => $expectedFileChange) {
            [, $relativePath] = \explode('_temp_fixture_easy_testing', $path);
            $addedFile = $addedFiles[$relativePath] ?? null;
            [, $addedFilePathWithContentFilePath] = $addedFile ? \explode('_temp_fixture_easy_testing', $addedFile->getFilePath()) : null;
            $this->assertSame($relativePath, $addedFilePathWithContentFilePath);
            $realFileContent = $addedFile ? \trim($addedFile->getFileContent()) : null;
            $this->assertSame($expectedFileChange, $realFileContent);
            if (\file_exists($path)) {
                FileSystem::delete($path);
            }
        }
    }
    private function createFixtureDir(string $fileName) : void
    {
        $dirName = \dirname($fileName);
        if (!\file_exists($dirName)) {
            \mkdir($dirName, 0777, \true);
        }
    }
}
