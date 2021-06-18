<?php

declare(strict_types=1);

namespace Rector\Core\PHPStan\Reflection;

use PhpParser\Node;
use PhpParser\Node\Expr\FuncCall;
use PhpParser\Node\Stmt\Function_;
use PhpParser\Parser;
use PHPStan\Reflection\FunctionReflection;
use PHPStan\Reflection\MethodReflection;
use PHPStan\Reflection\Php\PhpFunctionReflection;
use Rector\Core\PhpParser\Node\BetterNodeFinder;
use Rector\NodeNameResolver\NodeNameResolver;
use Symplify\SmartFileSystem\SmartFileSystem;

final class VariadicAnalyzer
{
    public function __construct(
        private Parser $parser,
        private SmartFileSystem $smartFileSystem,
        private BetterNodeFinder $betterNodeFinder,
        private NodeNameResolver $nodeNameResolver
    ) {
    }

    public function hasVariadicParameters(MethodReflection | FunctionReflection $functionReflection): bool
    {
        $variants = $functionReflection->getVariants();

        foreach ($variants as $variant) {
            // can be any number of arguments → nothing to limit here
            if ($variant->isVariadic()) {
                return true;
            }
        }

        if ($functionReflection instanceof  PhpFunctionReflection) {
            $pathsFunctionName = explode('\\', $functionReflection->getName());
            $functionName = array_pop($pathsFunctionName);

            $fileName = (string) $functionReflection->getFileName();
            /** @var Node[] $contentNodes */
            $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));

            /** @var Function_ $function */
            $function = $this->betterNodeFinder->findFirst($contentNodes, function (Node $node) use (
                $functionName
            ): bool {
                if (! $node instanceof Function_) {
                    return false;
                }

                return $this->nodeNameResolver->isName($node, $functionName);
            });

            return (bool) $this->betterNodeFinder->findFirst($function->stmts, function (Node $node): bool {
                if (! $node instanceof FuncCall) {
                    return false;
                }

                return $this->nodeNameResolver->isNames($node, ['func_get_args', 'func_num_args', 'func_get_arg']);
            });
        }

        return false;
    }
}
