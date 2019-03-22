<?php declare(strict_types=1);

namespace Rector\PhpSpecToPHPUnit\Rector\ClassMethod;

use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\PhpSpecToPHPUnit\Naming\PhpSpecRenaming;
use Rector\PhpSpecToPHPUnit\Rector\AbstractPhpSpecToPHPUnitRector;

final class PhpSpecMethodToPHPUnitMethodRector extends AbstractPhpSpecToPHPUnitRector
{
    /**
     * @var PhpSpecRenaming
     */
    private $phpSpecRenaming;

    public function __construct(
        PhpSpecRenaming $phpSpecRenaming,
        string $objectBehaviorClass = 'PhpSpec\ObjectBehavior'
    ) {
        $this->phpSpecRenaming = $phpSpecRenaming;

        parent::__construct($objectBehaviorClass);
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ClassMethod::class];
    }

    /**
     * @param ClassMethod $node
     */
    public function refactor(Node $node): ?Node
    {
        if (! $this->isInPhpSpecBehavior($node)) {
            return null;
        }

        if ($this->isName($node, 'let')) {
            $this->processBeConstructed($node);
        } else {
            /** @var string $name */
            $this->processBeConstructed($node);
            $this->processTestMethod($node);
        }

        return $node;
    }

    private function processBeConstructed(ClassMethod $classMethod): void
    {
        if ($this->isName($classMethod, 'let')) {
            $classMethod->name = new Identifier('setUp');
            $this->makeProtected($classMethod);
        }
    }

    private function processTestMethod(ClassMethod $classMethod): void
    {
        // special case, @see https://johannespichler.com/writing-custom-phpspec-matchers/
        if ($this->isName($classMethod, 'getMatchers')) {
            return;
        }

        // change name to phpunit test case format
        $this->phpSpecRenaming->renameMethod($classMethod);
    }
}
