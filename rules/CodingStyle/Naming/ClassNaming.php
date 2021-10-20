<?php

declare (strict_types=1);
namespace Rector\CodingStyle\Naming;

use RectorPrefix20211020\Nette\Utils\Strings;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassLike;
use PhpParser\Node\Stmt\Function_;
use Rector\Testing\PHPUnit\StaticPHPUnitEnvironment;
use RectorPrefix20211020\Stringy\Stringy;
use Symplify\SmartFileSystem\SmartFileInfo;
final class ClassNaming
{
    /**
     * @see https://regex101.com/r/8BdrI3/1
     * @var string
     */
    private const INPUT_HASH_NAMING_REGEX = '#input_(.*?)_#';
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier $name
     */
    public function getVariableName($name) : string
    {
        $shortName = $this->getShortName($name);
        return \lcfirst($shortName);
    }
    /**
     * @param string|\PhpParser\Node\Name|\PhpParser\Node\Identifier|\PhpParser\Node\Stmt\ClassLike $name
     */
    public function getShortName($name) : string
    {
        if ($name instanceof \PhpParser\Node\Stmt\ClassLike) {
            if ($name->name === null) {
                return '';
            }
            return $this->getShortName($name->name);
        }
        if ($name instanceof \PhpParser\Node\Name || $name instanceof \PhpParser\Node\Identifier) {
            $name = $name->toString();
        }
        $name = \trim($name, '\\');
        return \RectorPrefix20211020\Nette\Utils\Strings::after($name, '\\', -1) ?: $name;
    }
    public function getNamespace(string $fullyQualifiedName) : ?string
    {
        $fullyQualifiedName = \trim($fullyQualifiedName, '\\');
        return \RectorPrefix20211020\Nette\Utils\Strings::before($fullyQualifiedName, '\\', -1) ?: null;
    }
    public function getNameFromFileInfo(\Symplify\SmartFileSystem\SmartFileInfo $smartFileInfo) : string
    {
        $basenameWithoutSuffix = $smartFileInfo->getBasenameWithoutSuffix();
        // remove PHPUnit fixture file prefix
        if (\Rector\Testing\PHPUnit\StaticPHPUnitEnvironment::isPHPUnitRun()) {
            $basenameWithoutSuffix = \RectorPrefix20211020\Nette\Utils\Strings::replace($basenameWithoutSuffix, self::INPUT_HASH_NAMING_REGEX, '');
        }
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($basenameWithoutSuffix);
        return (string) $stringy->upperCamelize();
    }
    /**
     * "some_function" → "someFunction"
     */
    public function createMethodNameFromFunction(\PhpParser\Node\Stmt\Function_ $function) : string
    {
        $functionName = (string) $function->name;
        $stringy = new \RectorPrefix20211020\Stringy\Stringy($functionName);
        return (string) $stringy->camelize();
    }
    public function replaceSuffix(string $content, string $oldSuffix, string $newSuffix) : string
    {
        if (\substr_compare($content, $oldSuffix, -\strlen($oldSuffix)) !== 0) {
            return $content . $newSuffix;
        }
        $contentWithoutOldSuffix = \RectorPrefix20211020\Nette\Utils\Strings::substring($content, 0, -\RectorPrefix20211020\Nette\Utils\Strings::length($oldSuffix));
        return $contentWithoutOldSuffix . $newSuffix;
    }
}
