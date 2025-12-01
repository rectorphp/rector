<?php

declare (strict_types=1);
namespace Rector\PostRector\Rector;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\NodeVisitorAbstract;
use Rector\ChangesReporting\ValueObject\RectorWithLineChange;
use Rector\PostRector\Contract\Rector\PostRectorInterface;
use Rector\ValueObject\Application\File;
use RectorPrefix202512\Webmozart\Assert\Assert;
abstract class AbstractPostRector extends NodeVisitorAbstract implements PostRectorInterface
{
    /**
     * @var \Rector\ValueObject\Application\File|null
     */
    private $file = null;
    /**
     * @param Stmt[] $stmts
     */
    public function shouldTraverse(array $stmts): bool
    {
        return \true;
    }
    public function setFile(File $file): void
    {
        $this->file = $file;
    }
    public function getFile(): File
    {
        Assert::isInstanceOf($this->file, File::class);
        return $this->file;
    }
    protected function addRectorClassWithLine(Node $node): void
    {
        Assert::isInstanceOf($this->file, File::class);
        $rectorWithLineChange = new RectorWithLineChange(static::class, $node->getStartLine());
        $this->file->addRectorClassWithLine($rectorWithLineChange);
    }
}
