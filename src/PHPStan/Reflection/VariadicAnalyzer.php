<?php

declare (strict_types=1);
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
use RectorPrefix20210620\Symplify\SmartFileSystem\SmartFileSystem;
final class VariadicAnalyzer
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;
    /**
     * @var \Symplify\SmartFileSystem\SmartFileSystem
     */
    private $smartFileSystem;
    /**
     * @var \Rector\Core\PhpParser\Node\BetterNodeFinder
     */
    private $betterNodeFinder;
    /**
     * @var \Rector\NodeNameResolver\NodeNameResolver
     */
    private $nodeNameResolver;
    public function __construct(\PhpParser\Parser $parser, \RectorPrefix20210620\Symplify\SmartFileSystem\SmartFileSystem $smartFileSystem, \Rector\Core\PhpParser\Node\BetterNodeFinder $betterNodeFinder, \Rector\NodeNameResolver\NodeNameResolver $nodeNameResolver)
    {
        $this->parser = $parser;
        $this->smartFileSystem = $smartFileSystem;
        $this->betterNodeFinder = $betterNodeFinder;
        $this->nodeNameResolver = $nodeNameResolver;
    }
    /**
     * @param \PHPStan\Reflection\MethodReflection|\PHPStan\Reflection\FunctionReflection $functionReflection
     */
    public function hasVariadicParameters($functionReflection) : bool
    {
        $variants = $functionReflection->getVariants();
        foreach ($variants as $variant) {
            // can be any number of arguments → nothing to limit here
            if ($variant->isVariadic()) {
                return \true;
            }
        }
        if ($functionReflection instanceof \PHPStan\Reflection\Php\PhpFunctionReflection) {
            $pathsFunctionName = \explode('\\', $functionReflection->getName());
            $functionName = \array_pop($pathsFunctionName);
            $fileName = (string) $functionReflection->getFileName();
            /** @var Node[] $contentNodes */
            $contentNodes = $this->parser->parse($this->smartFileSystem->readFile($fileName));
            /** @var Function_ $function */
            $function = $this->betterNodeFinder->findFirst($contentNodes, function (\PhpParser\Node $node) use($functionName) : bool {
                if (!$node instanceof \PhpParser\Node\Stmt\Function_) {
                    return \false;
                }
                return $this->nodeNameResolver->isName($node, $functionName);
            });
            return (bool) $this->betterNodeFinder->findFirst($function->stmts, function (\PhpParser\Node $node) : bool {
                if (!$node instanceof \PhpParser\Node\Expr\FuncCall) {
                    return \false;
                }
                return $this->nodeNameResolver->isNames($node, ['func_get_args', 'func_num_args', 'func_get_arg']);
            });
        }
        return \false;
    }
}
