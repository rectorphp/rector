<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticMethod
{
    /**
     * @var AnotherClass
     */
    private $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function getMoreWhatever($value)
    {
        return $this->anotherClass->getAnotherMethod($value);
    }

    public function getMoreWhateverDoc($value)
    {
        return $this->anotherClass->getAnotherMethodDoc($value);
    }
}

class AnotherClass
{
    public function getAnotherMethod($value): int
    {
        return $value;
    }

    /**
     * @return false|true
     */
    public function getAnotherMethodDoc($value)
    {
        return $value;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\FunctionLike\ReturnTypeDeclarationRector\Fixture;

class KnownStaticMethod
{
    /**
     * @var AnotherClass
     */
    private $anotherClass;

    public function __construct(AnotherClass $anotherClass)
    {
        $this->anotherClass = $anotherClass;
    }

    public function getMoreWhatever($value): int
    {
        return $this->anotherClass->getAnotherMethod($value);
    }

    public function getMoreWhateverDoc($value): bool
    {
        return $this->anotherClass->getAnotherMethodDoc($value);
    }
}

class AnotherClass
{
    public function getAnotherMethod($value): int
    {
        return $value;
    }

    /**
     * @return false|true
     */
    public function getAnotherMethodDoc($value): bool
    {
        return $value;
    }
}

?>
