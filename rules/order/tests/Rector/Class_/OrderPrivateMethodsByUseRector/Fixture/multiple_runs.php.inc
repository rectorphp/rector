<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

class MultipleRunsClass
{
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node->params = $this->getSortedParams($node);

        return $node;
    }

    public function configure(array $configuration): void
    {

    }

    private function getSortedParams(ClassMethod $classMethod): array
    {
        $params = $classMethod->getParams();
        usort($params, function (Param $firstParam, Param $secondParam) {
            $firstParamType = $this->getParamType($firstParam);
            $secondParamType = $this->getParamType($secondParam);

            return $this->getShortName($firstParamType) <=> $this->getShortName($secondParamType);
        });

        return $params;
    }

    private function hasPrimitiveDataTypeParam(ClassMethod $classMethod): bool
    {

    }

    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $smartFileInfo = $classMethod->getAttribute(AttributeKey::FILE_INFO);
        if ($this->isFileInfoMatch($smartFileInfo)) {
            return true;
        }

        if ($this->hasPrimitiveDataTypeParam($classMethod)) {
            return true;
        }

        return $this->hasParamWithNoType($classMethod);
    }

    private function getParamType(Param $param)
    {

    }

    private function hasParamWithNoType(ClassMethod $classMethod): bool
    {

    }

    private function isFileInfoMatch(SmartFileInfo $smartFileInfo): bool
    {
        foreach ($this->skipPatterns as $pattern) {
            if (fnmatch($pattern, $smartFileInfo->getRelativeFilePath(), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }
}

?>
-----
<?php

namespace Rector\Order\Tests\Rector\Class_\OrderPrivateMethodsByUseRector\Fixture;

use PhpParser\Node;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\ClassMethod;
use Rector\NodeTypeResolver\Node\AttributeKey;
use Symplify\SmartFileSystem\SmartFileInfo;

class MultipleRunsClass
{
    public function refactor(Node $node): ?Node
    {
        if ($this->shouldSkip($node)) {
            return null;
        }

        $node->params = $this->getSortedParams($node);

        return $node;
    }

    public function configure(array $configuration): void
    {

    }
    private function shouldSkip(ClassMethod $classMethod): bool
    {
        $smartFileInfo = $classMethod->getAttribute(AttributeKey::FILE_INFO);
        if ($this->isFileInfoMatch($smartFileInfo)) {
            return true;
        }

        if ($this->hasPrimitiveDataTypeParam($classMethod)) {
            return true;
        }

        return $this->hasParamWithNoType($classMethod);
    }

    private function getSortedParams(ClassMethod $classMethod): array
    {
        $params = $classMethod->getParams();
        usort($params, function (Param $firstParam, Param $secondParam) {
            $firstParamType = $this->getParamType($firstParam);
            $secondParamType = $this->getParamType($secondParam);

            return $this->getShortName($firstParamType) <=> $this->getShortName($secondParamType);
        });

        return $params;
    }
    private function isFileInfoMatch(SmartFileInfo $smartFileInfo): bool
    {
        foreach ($this->skipPatterns as $pattern) {
            if (fnmatch($pattern, $smartFileInfo->getRelativeFilePath(), FNM_NOESCAPE)) {
                return true;
            }
        }

        return false;
    }

    private function hasPrimitiveDataTypeParam(ClassMethod $classMethod): bool
    {

    }

    private function hasParamWithNoType(ClassMethod $classMethod): bool
    {

    }

    private function getParamType(Param $param)
    {

    }
}

?>
