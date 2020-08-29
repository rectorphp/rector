<?php

declare(strict_types=1);

namespace Rector\Polyfill;

use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Polyfill\Contract\ConditionInterface;
use Rector\Polyfill\ValueObject\BinaryToVersionCompareCondition;
use Rector\Polyfill\ValueObject\VersionCompareCondition;

final class ConditionEvaluator
{
    /**
     * @return bool|int|null
     */
    public function evaluate(ConditionInterface $condition)
    {
        if ($condition instanceof VersionCompareCondition) {
            return $this->evaluateVersionCompareCondition($condition);
        }

        if ($condition instanceof BinaryToVersionCompareCondition) {
            return $this->evaluateBinaryToVersionCompareCondition($condition);
        }

        return null;
    }

    /**
     * @return bool|int
     */
    private function evaluateVersionCompareCondition(VersionCompareCondition $versionCompareCondition)
    {
        $compareSign = $versionCompareCondition->getCompareSign();
        if ($compareSign !== null) {
            return version_compare(
                $versionCompareCondition->getFirstVersion(),
                $versionCompareCondition->getSecondVersion(),
                $compareSign
            );
        }

        return version_compare(
            $versionCompareCondition->getFirstVersion(),
            $versionCompareCondition->getSecondVersion()
        );
    }

    private function evaluateBinaryToVersionCompareCondition(
        BinaryToVersionCompareCondition $binaryToVersionCompareCondition
    ): bool {
        $versionCompareResult = $this->evaluateVersionCompareCondition(
            $binaryToVersionCompareCondition->getVersionCompareCondition()
        );

        if ($binaryToVersionCompareCondition->getBinaryClass() === Identical::class) {
            return $binaryToVersionCompareCondition->getExpectedValue() === $versionCompareResult;
        }

        if ($binaryToVersionCompareCondition->getBinaryClass() === NotIdentical::class) {
            return $binaryToVersionCompareCondition->getExpectedValue() !== $versionCompareResult;
        }

        if ($binaryToVersionCompareCondition->getBinaryClass() === Equal::class) {
            // weak comparison on purpose
            return $binaryToVersionCompareCondition->getExpectedValue() == $versionCompareResult;
        }

        if ($binaryToVersionCompareCondition->getBinaryClass() === NotEqual::class) {
            // weak comparison on purpose
            return $binaryToVersionCompareCondition->getExpectedValue() != $versionCompareResult;
        }

        throw new ShouldNotHappenException();
    }
}
