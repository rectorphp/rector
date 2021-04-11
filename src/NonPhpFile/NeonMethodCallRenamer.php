<?php
declare(strict_types=1);

namespace Rector\Core\NonPhpFile;

use Rector\Core\Contract\Processor\FileProcessorInterface;
use Rector\Core\ValueObject\NonPhpFile\NonPhpFileChange;
use Rector\Renaming\Configuration\MethodCallRenameCollector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @todo maybe it should be in rector-nette package, this is just prototype
 */
class NeonMethodCallRenamer implements FileProcessorInterface
{
    /**
     * @var MethodCallRenameCollector
     */
    private $methodCallRenameCollector;

    public function __construct(MethodCallRenameCollector $methodCallRenameCollector)
    {
        $this->methodCallRenameCollector = $methodCallRenameCollector;
    }

    public function process(SmartFileInfo $smartFileInfo): ?NonPhpFileChange
    {
        $oldContent = $newContent = $smartFileInfo->getContents();

        foreach ($this->methodCallRenameCollector->getMethodCallRenames() as $methodCallRename) {
            $oldObjectType = $methodCallRename->getOldObjectType();

            // TODO potrebujeme vsetkych childov tejto classy - v rename method rectorovi moze byt interface, ale v config.neon uz nejaka implementacia
            $className = str_replace('\\', '\\\\', $oldObjectType->getClassName());
            $oldMethodName = $methodCallRename->getOldMethod();

            // @see https://regex101.com/r/9xA96W/1 TODO service moze byt bez ale aj so zatvorkami, mozno aj metoda bez parametrov moze byt bez zatvoriek, treba zistit
            $pattern = '/\n(.*?)(class|factory): ' . $className . '\n\1setup:(.*?)- ' . $oldMethodName . '\(/s';
            while (preg_match($pattern, $newContent, $matches)) {
                $replacedMatch = str_replace($methodCallRename->getOldMethod(), $methodCallRename->getNewMethod(), $matches[0]);
                $newContent = str_replace($matches[0], $replacedMatch, $newContent);
            }
        }

        return new NonPhpFileChange($oldContent, $newContent);
    }

    public function supports(SmartFileInfo $smartFileInfo): bool
    {
        return in_array($smartFileInfo->getExtension(), $this->getSupportedFileExtensions());
    }

    public function getSupportedFileExtensions(): array
    {
        return ['neon'];
    }
}
