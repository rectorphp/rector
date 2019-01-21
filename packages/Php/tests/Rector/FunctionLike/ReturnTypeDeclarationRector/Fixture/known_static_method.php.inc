<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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

    public function getMoreWhatever()
    {
        return $this->anotherClass->getAnotherMethod();
    }

    public function getMoreWhateverDoc()
    {
        return $this->anotherClass->getAnotherMethodDoc();
    }
}

class AnotherClass
{
    public function getAnotherMethod(): int
    {

    }

    /**
     * @return false|true
     */
    public function getAnotherMethodDoc()
    {

    }
}

?>
-----
<?php

namespace Rector\Php\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture;

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

    public function getMoreWhatever(): int
    {
        return $this->anotherClass->getAnotherMethod();
    }

    public function getMoreWhateverDoc(): bool
    {
        return $this->anotherClass->getAnotherMethodDoc();
    }
}

class AnotherClass
{
    public function getAnotherMethod(): int
    {

    }

    /**
     * @return false|true
     */
    public function getAnotherMethodDoc(): bool
    {

    }
}

?>
