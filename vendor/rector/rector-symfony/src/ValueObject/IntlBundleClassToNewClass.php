<?php

declare (strict_types=1);
namespace Rector\Symfony\ValueObject;

use Rector\Validation\RectorAssert;
use RectorPrefix202506\Webmozart\Assert\Assert;
final class IntlBundleClassToNewClass
{
    /**
     * @readonly
     */
    private string $oldClass;
    /**
     * @readonly
     */
    private string $newClass;
    /**
     * @var array<string, string>
     * @readonly
     */
    private array $oldToNewMethods;
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
