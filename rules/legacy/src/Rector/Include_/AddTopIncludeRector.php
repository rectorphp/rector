<?php

declare(strict_types=1);

namespace Rector\Legacy\Rector\Include_;

use PhpParser\Node\Expr\BinaryOp\Concat;
use PhpParser\Node\Expr\Include_;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Expression;
use PhpParser\Node\Stmt\Nop;
use Rector\Core\RectorDefinition\CodeSample;
use Rector\Core\RectorDefinition\RectorDefinition;
use Rector\FileSystemRector\Rector\AbstractFileSystemRector;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * @see https://github.com/rectorphp/rector/issues/3679
 *
 * @see \Rector\Legacy\Tests\Rector\Include_\AddTopIncludeRector\AddTopIncludeRectorTest
 */
final class AddTopIncludeRector extends AbstractFileSystemRector
{
    /**
     * @var String_
     */
    private $autoloadFilePathString;

    /**
     * @var string[]
     */
    private $patterns = [];

    public function __construct(string $autoloadFilePath = '/autoload.php', array $match = [])
    {
        $this->autoloadFilePathString = new String_($autoloadFilePath);
        $this->patterns = $match;
    }

    public function getDefinition(): RectorDefinition
    {
        return new RectorDefinition('Adds an include file at the top of matching files, except class definitions', [
            new CodeSample(
                <<<'PHP'
if (isset($_POST['csrf'])) {
    processPost($_POST);
}
PHP,
                <<<'PHP'
require_once __DIR__ . '/../autoloader.php';

if (isset($_POST['csrf'])) {
    processPost($_POST);
}
PHP
            ),
        ]);
    }

    public function refactor(SmartFileInfo $smartFileInfo): void
    {
        if (! $this->isFileInfoMatch($smartFileInfo->getRelativeFilePath())) {
            return;
        }

        $nodes = $this->parseFileInfoToNodes($smartFileInfo);

        // we are done if there is a class definition in this file
        if ($this->betterNodeFinder->hasInstancesOf($nodes, [Class_::class])) {
            return;
        }

        if ($this->hasIncludeAlready($nodes)) {
            return;
        }

        // add the include to the statements and print it
        array_unshift($nodes, new Nop());
        array_unshift($nodes, new Expression($this->createInclude()));

        $this->printNodesToFilePath($nodes, $smartFileInfo->getRelativeFilePath());
    }

    /**
     * Match file against matches, no patterns provided, then it matches
     */
    private function isFileInfoMatch(string $path): bool
    {
        if ($this->patterns === []) {
            return true;
        }

        foreach ($this->patterns as $pattern) {
            if (fnmatch($pattern, $path, FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function createInclude(): Include_
    {
        $filePathConcat = new Concat(new Dir(), $this->autoloadFilePathString);

        return new Include_($filePathConcat, Include_::TYPE_REQUIRE_ONCE);
    }

    /**
     * Find all includes and see if any match what we want to insert
     */
    private function hasIncludeAlready(array $nodes): bool
    {
        /** @var Include_[] $includes */
        $includes = $this->betterNodeFinder->findInstanceOf($nodes, Include_::class);
        foreach ($includes as $include) {
            if ($this->isTopFileInclude($include)) {
                return true;
            }
        }

        return false;
    }

    private function isTopFileInclude(Include_ $include): bool
    {
        return $this->areNodesEqual($include->expr, $this->createInclude()->expr);
    }
}
