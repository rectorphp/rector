<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Scalar\MagicConst\Dir;
use PhpParser\Node\Scalar\String_;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Application\ConstantNodeCollector;
use Rector\NodeTypeResolver\Node\Attribute;
use Rector\PhpParser\Node\Resolver\NameResolver;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class ConstExprEvaluatorFactory
{
    /**
     * @var ConstantNodeCollector
     */
    private $constantNodeCollector;

    /**
     * @var NameResolver
     */
    private $nameResolver;

    public function __construct(ConstantNodeCollector $constantNodeCollector, NameResolver $nameResolver)
    {
        $this->constantNodeCollector = $constantNodeCollector;
        $this->nameResolver = $nameResolver;
    }

    public function create(): ConstExprEvaluator
    {
        return new ConstExprEvaluator(function (Expr $expr): ?string {
            // resolve "__DIR__"
            if ($expr instanceof Dir) {
                $fileInfo = $expr->getAttribute(Attribute::FILE_INFO);
                if (! $fileInfo instanceof SmartFileInfo) {
                    throw new ShouldNotHappenException();
                }

                return $fileInfo->getPath();
            }

            // resolve "SomeClass::SOME_CONST"
            if ($expr instanceof ClassConstFetch) {
                return $this->resolveClassConstFetch($expr);
            }

            return null;
        });
    }

    private function resolveClassConstFetch(ClassConstFetch $classConstFetchNode): string
    {
        $class = $this->nameResolver->resolve($classConstFetchNode->class);
        $constant = $this->nameResolver->resolve($classConstFetchNode->name);

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

        $constNodeValue = ($classConstNode->consts[0]->value);

        // @todo recurse - use service instead of factory 3rd party
        if ($constNodeValue instanceof String_) {
            return $constNodeValue->value;
        }

        // @todo
        throw new ShouldNotHappenException('Wip in ' . __METHOD__);
    }
}
