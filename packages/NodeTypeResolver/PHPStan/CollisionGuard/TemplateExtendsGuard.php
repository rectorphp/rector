<?php

declare (strict_types=1);
namespace Rector\NodeTypeResolver\PHPStan\CollisionGuard;

use RectorPrefix20211026\Nette\Utils\Strings;
use PhpParser\Node;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use Rector\Core\Application\FileProcessor;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use ReflectionClass;
use RectorPrefix20211026\Symplify\SmartFileSystem\SmartFileSystem;
final class TemplateExtendsGuard
{
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    public function __construct(\Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \RectorPrefix20211026\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem)
    {
        $this->betterNodeFinder = $betterNodeFinder;
        $this->smartFileSystem = $smartFileSystem;
    }
    /**
     * Thist needs to be checked early before `@mixin` check as
     * ReflectionProvider already hang when check class with `@template-extends`
     *
     * @see https://github.com/phpstan/phpstan/issues/3865 in PHPStan
     * @param Stmt[] $nodes
     */
    public function containsTemplateExtendsPhpDoc(array $nodes, string $currentFileName) : bool
    {
        return (bool) $this->betterNodeFinder->findFirst($nodes, function (\PhpParser\Node $node) use($currentFileName) : bool {
            if (!$node instanceof \PhpParser\Node\Name\FullyQualified) {
                return \false;
            }
            $className = $node->toString();
            // fix error in parallel test
            // use function_exists on purpose as using reflectionProvider broke the test in parallel
            if (\function_exists($className)) {
                return \false;
            }
            // use class_exists as PHPStan ReflectionProvider hang on check className with `@template-extends`
            if (!\class_exists($className)) {
                return \false;
            }
            // use native ReflectionClass as PHPStan ReflectionProvider hang on check className with `@template-extends`
            $reflectionClass = new \ReflectionClass($className);
            if ($reflectionClass->isInternal()) {
                return \false;
            }
            $fileName = (string) $reflectionClass->getFileName();
            if (!$this->smartFileSystem->exists($fileName)) {
                return \false;
            }
            // already checked in FileProcessor::parseFileInfoToLocalCache()
            if ($fileName === $currentFileName) {
                return \false;
            }
            $fileContents = $this->smartFileSystem->readFile($fileName);
            return (bool) \RectorPrefix20211026\Nette\Utils\Strings::match($fileContents, \Rector\Core\Application\FileProcessor::TEMPLATE_EXTENDS_REGEX);
        });
    }
}
