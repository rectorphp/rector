<?php declare(strict_types=1);

namespace Rector\PhpParser\Node\Value;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\MagicConst\File;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class ValueResolver
{
    /**
     * @var NameResolver
     */
    private $nameResolver;

    /**
     * @var ConstExprEvaluator
     */
    private $constExprEvaluator;

    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    public function __construct(NameResolver $nameResolver, ConstantNodeCollector $constantNodeCollector)
    {
        $this->nameResolver = $nameResolver;
        $this->constantNodeCollector = $constantNodeCollector;
    }

    /**
     * @return mixed|null
     */
    public function resolve(Expr $node)
    {
        return $this->getConstExprEvaluator()->evaluateDirectly($node);
    }

    private function getConstExprEvaluator(): ConstExprEvaluator
    {
        if ($this->constExprEvaluator !== null) {
            return $this->constExprEvaluator;
        }

        $this->constExprEvaluator = new ConstExprEvaluator(function (Expr $expr): ?string {
            // resolve "__DIR__"
            if ($expr instanceof Dir) {
                $fileInfo = $expr->getAttribute(Attribute::FILE_INFO);
                if (! $fileInfo instanceof SmartFileInfo) {
                    throw new ShouldNotHappenException();
                }

                return $fileInfo->getPath();
            }

            if ($expr instanceof File) {
                $fileInfo = $expr->getAttribute(Attribute::FILE_INFO);
                if (! $fileInfo instanceof SmartFileInfo) {
                    throw new ShouldNotHappenException();
                }

                return $fileInfo->getPathname();
            }

            // resolve "SomeClass::SOME_CONST"
            if ($expr instanceof ClassConstFetch) {
                return $this->resolveClassConstFetch($expr);
            }

            return null;
        });

        return $this->constExprEvaluator;
    }

    private function resolveClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        $class = $this->nameResolver->resolve($classConstFetchNode->class);
        $constant = $this->nameResolver->resolve($classConstFetchNode->name);

        if ($class === null) {
            throw new ShouldNotHappenException();
        }

        if ($constant === null) {
            throw new ShouldNotHappenException();
        }

        if ($class === 'self') {
            $class = (string) $classConstFetchNode->class->getAttribute(Attribute::CLASS_NAME);
        }

        if ($constant === 'class') {
            return $class;
        }

        $classConstNode = $this->constantNodeCollector->findConstant($constant, $class);

        if ($classConstNode === null) {
            // fallback to the name
            return $class . '::' . $constant;
        }

        return $this->constExprEvaluator->evaluateDirectly($classConstNode->consts[0]->value);
    }
}
