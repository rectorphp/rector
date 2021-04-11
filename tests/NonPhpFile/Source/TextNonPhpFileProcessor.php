<?php
declare(strict_types=1);


namespace Rector\Core\Tests\NonPhpFile\Source;


use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\NonPhpFile\NonPhpFileChange;
use Symplify\SmartFileSystem\SmartFileInfo;

final class TextNonPhpFileProcessor implements FileProcessorInterface
{

    public function process(SmartFileInfo $smartFileInfo): ?NonPhpFileChange
    {
        $oldContent = $smartFileInfo->getContents();
        $newContent = str_replace('Foo', 'Bar', $oldContent);

        return new NonPhpFileChange($oldContent, $newContent);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['txt'];
    }
}
