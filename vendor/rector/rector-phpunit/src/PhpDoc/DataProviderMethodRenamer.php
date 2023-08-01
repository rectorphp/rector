<?php

declare (strict_types=1);
namespace Rector\PHPUnit\PhpDoc;

use RectorPrefix202308\Nette\Utils\Strings;
use PhpParser\Node\Stmt\Class_;
use PHPStan\PhpDocParser\Ast\PhpDoc\GenericTagValueNode;
use Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory;
use Rector\BetterPhpDocParser\ValueObject\PhpDocAttributeKey;
final class DataProviderMethodRenamer
{
    /**
     * @readonly
     * @var \Rector\BetterPhpDocParser\PhpDocInfo\PhpDocInfoFactory
     */
    private $phpDocInfoFactory;
    public function __construct(PhpDocInfoFactory $phpDocInfoFactory)
    {
        $this->phpDocInfoFactory = $phpDocInfoFactory;
    }
    public function removeTestPrefix(Class_ $class) : void
    {
        foreach ($class->getMethods() as $classMethod) {
            $phpDocInfo = $this->phpDocInfoFactory->createFromNodeOrEmpty($classMethod);
            foreach ($phpDocInfo->getTagsByName('dataProvider') as $phpDocTagNode) {
                if (!$phpDocTagNode->value instanceof GenericTagValueNode) {
                    continue;
                }
                $oldMethodName = $phpDocTagNode->value->value;
                if (\strncmp($oldMethodName, 'test', \strlen('test')) !== 0) {
                    continue;
                }
                $newMethodName = $this->createMethodNameWithoutPrefix($oldMethodName, 'test');
                $phpDocTagNode->value->value = Strings::replace($oldMethodName, '#' . \preg_quote($oldMethodName, '#') . '#', $newMethodName);
                // invoke reprint
                $phpDocTagNode->setAttribute(PhpDocAttributeKey::START_AND_END, null);
                $phpDocInfo->markAsChanged();
            }
        }
    }
    private function createMethodNameWithoutPrefix(string $methodName, string $prefix) : string
    {
        $newMethodName = Strings::substring($methodName, \strlen($prefix));
        return \lcfirst($newMethodName);
    }
}
