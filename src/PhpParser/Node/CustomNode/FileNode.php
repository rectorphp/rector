<?php

declare(strict_types=1);

namespace Rector\Core\PhpParser\Node\CustomNode;

use PhpParser\Node;
use PhpParser\NodeAbstract;
use Symplify\SmartFileSystem\SmartFileInfo;

/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/ed81c3ad0b9877e6122c79b4afda9d10f3994092
 */
final class FileNode extends NodeAbstract
{
    /**
     * @var Node[]
     */
    public $stmts = [];

    /**
     * @var SmartFileInfo
     */
    private $fileInfo;

    /**
     * @param Node[] $stmts
     */
    public function __construct(SmartFileInfo $fileInfo, array $stmts)
    {
        parent::__construct();

        $this->fileInfo = $fileInfo;
        $this->stmts = $stmts;
    }

    public function getType(): string
    {
        return 'FileNode';
    }

    /**
     * @return string[]
     */
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }

    public function getFileInfo(): SmartFileInfo
    {
        return $this->fileInfo;
    }
}
