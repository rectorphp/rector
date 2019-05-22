<?php declare(strict_types=1);

namespace Rector\CodingStyle\Application;

use Nette\Utils\Strings;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\Stmt\UseUse;
use Rector\CodingStyle\Imports\UsedImportsResolver;

final class UseImportsAdder
{
    /**
     * @var UsedImportsResolver
     */
    private $usedImportsResolver;

    public function __construct(UsedImportsResolver $usedImportsResolver)
    {
        $this->usedImportsResolver = $usedImportsResolver;
    }

    /**
     * @param Stmt[] $stmts
     * @param string[] $useImports
     * @param string[] $functionUseImports
     * @return Stmt[]
     */
    public function addImportsToStmts(array $stmts, array $useImports, array $functionUseImports): array
    {
        $existingUseImports = $this->usedImportsResolver->resolveForStmts($stmts);
        $existingFunctionUseImports = $this->usedImportsResolver->resolveFunctionImportsForStmts($stmts);

        $useImports = array_unique($useImports);
        $functionUseImports = array_unique($functionUseImports);

        $useImports = array_diff($useImports, $existingUseImports);
        $functionUseImports = array_diff($functionUseImports, $existingFunctionUseImports);

        $newUses = $this->createUses($useImports, $functionUseImports, null);

        return array_merge($newUses, $stmts);
    }

    /**
     * @param string[] $useImports
     * @param string[] $functionUseImports
     */
    public function addImportsToNamespace(Namespace_ $namespace, array $useImports, array $functionUseImports): void
    {
        $namespaceName = $this->getNamespaceName($namespace);

        $existingUseImports = $this->usedImportsResolver->resolveForNode($namespace);
        $existingFunctionUseImports = $this->usedImportsResolver->resolveFunctionImportsForNode($namespace);

        $useImports = array_unique($useImports);
        $functionUseImports = array_unique($functionUseImports);

        $useImports = array_diff($useImports, $existingUseImports);
        $functionUseImports = array_diff($functionUseImports, $existingFunctionUseImports);

        $newUses = $this->createUses($useImports, $functionUseImports, $namespaceName);
        $namespace->stmts = array_merge($newUses, $namespace->stmts);
    }

    private function getNamespaceName(Namespace_ $namespace): ?string
    {
        if ($namespace->name === null) {
            return null;
        }

        return $namespace->name->toString();
    }

    private function isCurrentNamespace(?string $namespaceName, string $useImports): bool
    {
        if ($namespaceName === null) {
            return false;
        }

        $afterCurrentNamespace = Strings::after($useImports, $namespaceName . '\\');
        if (! $afterCurrentNamespace) {
            return false;
        }

        return ! Strings::contains($afterCurrentNamespace, '\\');
    }

    /**
     * @param string[] $useImports
     * @param string[] $functionUseImports
     * @return Use_[]
     */
    private function createUses(array $useImports, array $functionUseImports, ?string $namespaceName): array
    {
        $newUses = [];

        foreach ($useImports as $useImport) {
            if ($this->isCurrentNamespace($namespaceName, $useImport)) {
                continue;
            }

            // already imported in previous cycle
            $useUse = new UseUse(new Name($useImport));
            $newUses[] = new Use_([$useUse]);
        }

        foreach ($functionUseImports as $functionUseImport) {
            if ($this->isCurrentNamespace($namespaceName, $functionUseImport)) {
                continue;
            }

            // already imported in previous cycle
            $useUse = new UseUse(new Name($functionUseImport), null, Use_::TYPE_FUNCTION);
            $newUses[] = new Use_([$useUse]);
        }
        return $newUses;
    }
}
