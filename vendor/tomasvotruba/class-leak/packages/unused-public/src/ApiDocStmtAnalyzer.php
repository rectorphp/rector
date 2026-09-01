<?php

declare (strict_types=1);
namespace RectorPrefix202609\TomasVotruba\UnusedPublic;

use PhpParser\Comment\Doc;
use PhpParser\Node\Stmt;
use PHPStan\PhpDoc\ResolvedPhpDocBlock;
use PHPStan\Reflection\ClassReflection;
final class ApiDocStmtAnalyzer
{
    public function isApiDoc(Stmt $stmt, ClassReflection $classReflection): bool
    {
        if ($classReflection->getResolvedPhpDoc() instanceof ResolvedPhpDocBlock) {
            $resolvedPhpDoc = $classReflection->getResolvedPhpDoc();
            if (strpos($resolvedPhpDoc->getPhpDocString(), '@api') !== \false) {
                return \true;
            }
        }
        $docComment = $stmt->getDocComment();
        if (!$docComment instanceof Doc) {
            return \false;
        }
        return $this->isApiDocComment($docComment->getText());
    }
    public function isApiDocComment(string $docComment): bool
    {
        return strpos($docComment, '@api') !== \false;
    }
}
