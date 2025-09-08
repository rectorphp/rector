<?php

declare (strict_types=1);
namespace Rector\Assert\NodeAnalyzer;

use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Name;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PhpParser\PrettyPrinter\Standard;
use Rector\Assert\Enum\AssertClassName;
final class ExistingAssertStaticCallResolver
{
    /**
     * @return string[]
     */
    public function resolve(ClassMethod $classMethod): array
    {
        if ($classMethod->stmts === null) {
            return [];
        }
        $existingAssertCallHashes = [];
        $standard = new Standard();
        foreach ($classMethod->stmts as $currentStmt) {
            if (!$currentStmt instanceof Expression) {
                continue;
            }
            if (!$currentStmt->expr instanceof StaticCall) {
                continue;
            }
            $staticCall = $currentStmt->expr;
            if (!$staticCall->class instanceof Name) {
                continue;
            }
            if (!in_array($staticCall->class->toString(), [AssertClassName::WEBMOZART, AssertClassName::BEBERLEI], \true)) {
                continue;
            }
            $existingAssertCallHashes[] = $standard->prettyPrintExpr($staticCall);
        }
        return $existingAssertCallHashes;
    }
}
