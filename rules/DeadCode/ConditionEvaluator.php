<?php

declare (strict_types=1);
namespace Rector\DeadCode;

use PhpParser\Node\Expr\BinaryOp\Equal;
use PhpParser\Node\Expr\BinaryOp\Identical;
use PhpParser\Node\Expr\BinaryOp\NotEqual;
use PhpParser\Node\Expr\BinaryOp\NotIdentical;
use Rector\Core\Exception\ShouldNotHappenException;
use Rector\Core\Php\PhpVersionProvider;
use Rector\DeadCode\Contract\ConditionInterface;
use Rector\DeadCode\ValueObject\BinaryToVersionCompareCondition;
use Rector\DeadCode\ValueObject\VersionCompareCondition;
final class ConditionEvaluator
{
    /**
     * @readonly
     * @var \Rector\Core\Php\PhpVersionProvider
     */
    private $phpVersionProvider;
    public function __construct(\Rector\Core\Php\PhpVersionProvider $phpVersionProvider)
    {
        $this->phpVersionProvider = $phpVersionProvider;
    }
    /**
     * @return bool|int|null
     */
    public function evaluate(\Rector\DeadCode\Contract\ConditionInterface $condition)
    {
        if ($condition instanceof \Rector\DeadCode\ValueObject\VersionCompareCondition) {
            return $this->evaluateVersionCompareCondition($condition);
        }
        if ($condition instanceof \Rector\DeadCode\ValueObject\BinaryToVersionCompareCondition) {
            return $this->isEvaluedAsTrue($condition);
        }
        return null;
    }
    /**
     * @return bool|int|null
     */
    private function evaluateVersionCompareCondition(\Rector\DeadCode\ValueObject\VersionCompareCondition $versionCompareCondition)
    {
        $compareSign = $versionCompareCondition->getCompareSign();
        if ($compareSign !== null) {
            if ($compareSign === '<' && $this->phpVersionProvider->provide() < $versionCompareCondition->getSecondVersion()) {
                return null;
            }
            return \version_compare((string) $versionCompareCondition->getFirstVersion(), (string) $versionCompareCondition->getSecondVersion(), $compareSign);
        }
        return \version_compare((string) $versionCompareCondition->getFirstVersion(), (string) $versionCompareCondition->getSecondVersion());
    }
    private function isEvaluedAsTrue(\Rector\DeadCode\ValueObject\BinaryToVersionCompareCondition $binaryToVersionCompareCondition) : bool
    {
        $versionCompareResult = $this->evaluateVersionCompareCondition($binaryToVersionCompareCondition->getVersionCompareCondition());
        if ($binaryToVersionCompareCondition->getBinaryClass() === \PhpParser\Node\Expr\BinaryOp\Identical::class) {
            return $binaryToVersionCompareCondition->getExpectedValue() === $versionCompareResult;
        }
        if ($binaryToVersionCompareCondition->getBinaryClass() === \PhpParser\Node\Expr\BinaryOp\NotIdentical::class) {
            return $binaryToVersionCompareCondition->getExpectedValue() !== $versionCompareResult;
        }
        if ($binaryToVersionCompareCondition->getBinaryClass() === \PhpParser\Node\Expr\BinaryOp\Equal::class) {
            // weak comparison on purpose
            return $binaryToVersionCompareCondition->getExpectedValue() === $versionCompareResult;
        }
        if ($binaryToVersionCompareCondition->getBinaryClass() === \PhpParser\Node\Expr\BinaryOp\NotEqual::class) {
            // weak comparison on purpose
            return $binaryToVersionCompareCondition->getExpectedValue() !== $versionCompareResult;
        }
        throw new \Rector\Core\Exception\ShouldNotHappenException();
    }
}
