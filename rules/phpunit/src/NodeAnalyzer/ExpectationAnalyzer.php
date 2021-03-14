<?php
declare(strict_types=1);


namespace Rector\PHPUnit\NodeAnalyzer;

use PhpParser\Node\Expr\MethodCall;

final class ExpectationAnalyzer
{
    /**
     * @var TestsNodeAnalyzer
     */
    private $testsNodeAnalyzer;

    public function __construct(TestsNodeAnalyzer $testsNodeAnalyzer)
    {
        $this->testsNodeAnalyzer = $testsNodeAnalyzer;
    }

    public function isValidExpectsCall(MethodCall $expr): bool
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodName($expr, 'expects')) {
            return false;
        }

        if (count($expr->args) !== 1) {
            return false;
        }

        return true;
    }

    public function isValidAtCall(MethodCall $expr): bool
    {
        if (!$this->testsNodeAnalyzer->isPHPUnitMethodName($expr, 'at')) {
            return false;
        }

        if (count($expr->args) !== 1) {
            return false;
        }

        return true;
    }
}
