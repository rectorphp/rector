<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use Rector\Core\Validation\RectorAssert;
use RectorPrefix202305\Webmozart\Assert\Assert;
final class IntlBundleClassToNewClass
{
    /**
     * @readonly
     * @var string
     */
    private $oldClass;
    /**
     * @readonly
     * @var string
     */
    private $newClass;
    /**
     * @var array<string, string>
     * @readonly
     */
    private $oldToNewMethods;
    /**
     * @param array<string, string> $oldToNewMethods
     */
    public function __construct(string $oldClass, string $newClass, array $oldToNewMethods)
    {
        $this->oldClass = $oldClass;
        $this->newClass = $newClass;
        $this->oldToNewMethods = $oldToNewMethods;
        RectorAssert::className($oldClass);
        RectorAssert::className($newClass);
        Assert::allString($oldToNewMethods);
        Assert::allString(\array_keys($oldToNewMethods));
    }
    public function getOldClass() : string
    {
        return $this->oldClass;
    }
    public function getNewClass() : string
    {
        return $this->newClass;
    }
    /**
     * @return array<string, string>
     */
    public function getOldToNewMethods() : array
    {
        return $this->oldToNewMethods;
    }
}
