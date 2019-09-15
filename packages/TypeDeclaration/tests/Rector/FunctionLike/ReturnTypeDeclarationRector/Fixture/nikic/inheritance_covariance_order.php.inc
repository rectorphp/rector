<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\InheritanceOrder;

class ReorderedCCovariance extends ReorderedACovariance {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "ReorderedACovariance" instead, which is less accurate but valid.
     *
     * @return ReorderedCCovariance
     */
    public function test() {
        return $this;
    }
}

class ReorderedACovariance {
    /** @return ReorderedACovariance */
    public function test() {
        return $this;
    }
}

?>
-----
<?php

namespace Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\InheritanceOrder;

class ReorderedCCovariance extends ReorderedACovariance {
    /**
     * Technically valid return type, but against PHP's variance restrictions.
     * We use "ReorderedACovariance" instead, which is less accurate but valid.
     *
     * @return ReorderedCCovariance
     */
    public function test(): \Rector\TypeDeclaration\Tests\Rector\ClassMethod\ReturnTypeDeclarationRector\Fixture\InheritanceOrder\ReorderedACovariance {
        return $this;
    }
}

class ReorderedACovariance {
    /** @return ReorderedACovariance */
    public function test(): self {
        return $this;
    }
}

?>
