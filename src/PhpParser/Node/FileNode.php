<?php

declare (strict_types=1);
namespace Rector\PhpParser\Node;

use PhpParser\Node;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\GroupUse;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
/**
 * Inspired by https://github.com/phpstan/phpstan-src/commit/ed81c3ad0b9877e6122c79b4afda9d10f3994092
 */
class FileNode extends Stmt
{
    /**
     * @var Stmt[]
     */
    public array $stmts;
    /**
     * @param Stmt[] $stmts
     */
    public function __construct(array $stmts)
    {
        $this->stmts = $stmts;
        $firstStmt = $stmts[0] ?? null;
        $attributes = $firstStmt instanceof Node ? $firstStmt->getAttributes() : [];
        parent::__construct($attributes);
    }
    /**
     * This triggers Printed method with "pFileNode" name
     * @see \Rector\PhpParser\Printer\BetterStandardPrinter::pStmt_FileNode()
     */
    public function getType(): string
    {
        return 'Stmt_FileNode';
    }
    /**
     * @return array<int, string>
     */
    public function getSubNodeNames(): array
    {
        return ['stmts'];
    }
    public function isNamespaced(): bool
    {
        foreach ($this->stmts as $stmt) {
            if ($stmt instanceof Namespace_) {
                return \true;
            }
        }
        return \false;
    }
    public function getNamespace(): ?Namespace_
    {
        /** @var Namespace_[] $namespaces */
        $namespaces = array_filter($this->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Namespace_);
        if (count($namespaces) === 1) {
            return current($namespaces);
        }
        return null;
    }
    /**
     * @return array<Use_|GroupUse>
     */
    public function getUsesAndGroupUses(): array
    {
        $rootNode = $this->getNamespace();
        if (!$rootNode instanceof Namespace_) {
            $rootNode = $this;
        }
        return array_filter($rootNode->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Use_ || $stmt instanceof GroupUse);
    }
    /**
     * @return Use_[]
     */
    public function getUses(): array
    {
        $rootNode = $this->getNamespace();
        if (!$rootNode instanceof Namespace_) {
            $rootNode = $this;
        }
        return array_filter($rootNode->stmts, static fn(Stmt $stmt): bool => $stmt instanceof Use_);
    }
}
