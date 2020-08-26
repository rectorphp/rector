<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;

class PhpDocInfo
{
    /**
     * @var StartEndValueObject[]
     */
    private $removedNodePositions = [];

    private function getRemovedNodesPositions(): array
    {
        if ($this->removedNodePositions !== []) {
            return $this->removedNodePositions;
        }

        /** @var AttributeAwareNodeInterface[] $removedNodes */
        $removedNodes = [];

        foreach ($removedNodes as $removedNode) {
            /** @var StartEndValueObject $removedPhpDocNodeInfo */
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::START_END);

            $seekPosition = $removedPhpDocNodeInfo->getStart();
            --$seekPosition;


            $this->removedNodePositions[] = new StartEndValueObject(
                $seekPosition - 1,
                $removedPhpDocNodeInfo->getEnd()
            );
        }

        return $this->removedNodePositions;
    }
}
?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\AddArrayReturnDocTypeRector\RectorUseCasesFixture;

use Rector\BetterPhpDocParser\Attributes\Attribute\Attribute;
use Rector\BetterPhpDocParser\Contract\PhpDocNode\AttributeAwareNodeInterface;
use Rector\BetterPhpDocParser\ValueObject\StartEndValueObject;

class PhpDocInfo
{
    /**
     * @var StartEndValueObject[]
     */
    private $removedNodePositions = [];

    /**
     * @return \Rector\BetterPhpDocParser\ValueObject\StartEndValueObject[]
     */
    private function getRemovedNodesPositions(): array
    {
        if ($this->removedNodePositions !== []) {
            return $this->removedNodePositions;
        }

        /** @var AttributeAwareNodeInterface[] $removedNodes */
        $removedNodes = [];

        foreach ($removedNodes as $removedNode) {
            /** @var StartEndValueObject $removedPhpDocNodeInfo */
            $removedPhpDocNodeInfo = $removedNode->getAttribute(Attribute::START_END);

            $seekPosition = $removedPhpDocNodeInfo->getStart();
            --$seekPosition;


            $this->removedNodePositions[] = new StartEndValueObject(
                $seekPosition - 1,
                $removedPhpDocNodeInfo->getEnd()
            );
        }

        return $this->removedNodePositions;
    }
}
?>
