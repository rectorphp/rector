<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class SomeCallableType
{
    private $code;
    /**
     * @param callable $code A callable(InputInterface $input, OutputInterface $output)
     */
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                $code = \Closure::bind($code, $this);
            }
        }
        $this->code = $code;
        return $this;
    }
}

?>
-----
<?php

namespace Rector\Tests\TypeDeclaration\Rector\Property\CompleteVarDocTypePropertyRector\Fixture;

final class SomeCallableType
{
    /**
     * @var callable|null
     */
    private $code;
    /**
     * @param callable $code A callable(InputInterface $input, OutputInterface $output)
     */
    public function setCode(callable $code)
    {
        if ($code instanceof \Closure) {
            $r = new \ReflectionFunction($code);
            if (null === $r->getClosureThis()) {
                $code = \Closure::bind($code, $this);
            }
        }
        $this->code = $code;
        return $this;
    }
}

?>
