<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance;

class ACovariance {
    /** @return ACovariance */
    public function test() {
        return $this;
    }
}

class CCovariance extends ACovariance {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "ACovariance" instead, which is less accurate but valid.
     *
     * @return CCovariance
     */
    public function test() {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance;

class ACovariance {
    /** @return ACovariance */
    public function test(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance\ACovariance {
        return $this;
    }
}

class CCovariance extends ACovariance {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "ACovariance" instead, which is less accurate but valid.
     *
     * @return CCovariance
     */
    public function test(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\Inheritance\ACovariance {
        return $this;
    }
}

?>
