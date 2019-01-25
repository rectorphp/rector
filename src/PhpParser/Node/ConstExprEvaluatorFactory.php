<?php declare(strict_types=1);

namespace Rector\PhpParser\Node;

use PhpParser\ConstExprEvaluator;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\ClassConstFetch;
use PhpParser\Node\Identifier;
use PhpParser\Node\Scalar\MagicConst\Dir;
use Rector\Exception\ShouldNotHappenException;
use Rector\NodeTypeResolver\Node\Attribute;
use Symplify\PackageBuilder\FileSystem\SmartFileInfo;

final class ConstExprEvaluatorFactory
{
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
        $class = $classConstFetchNode->class->getAttribute(Attribute::RESOLVED_NAME);
        if ($class === null) {
            return '';
        }

        /** @var Identifier $identifierNode */
        $identifierNode = $classConstFetchNode->name;

        $constant = $identifierNode->toString();

        return $class->toString() . '::' . $constant;
    }
}
