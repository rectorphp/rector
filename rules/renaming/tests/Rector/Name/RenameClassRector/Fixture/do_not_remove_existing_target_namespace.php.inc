<?php

namespace Acme {
   use Acme\Foo\DoNotUpdateExistingTargetNamespace;

   DoNotUpdateExistingTargetNamespace::run();
}

namespace Acme\Foo {

    /**
     * @deprecated Use Acme\Bar\DoNotUpdateExistingTargetNamespace instead!
     */
    final class DoNotUpdateExistingTargetNamespace
    {
        public static function run()
        {
            return 5;
        }
    }

}

namespace Acme\Bar {

    final class DoNotUpdateExistingTargetNamespace
    {
        public static function run()
        {
            return 5;
        }
    }
}

?>
-----
<?php

namespace Acme {
   use Acme\Foo\DoNotUpdateExistingTargetNamespace;

   \Acme\Bar\DoNotUpdateExistingTargetNamespace::run();
}

namespace Acme\Foo {

    /**
     * @deprecated Use Acme\Bar\DoNotUpdateExistingTargetNamespace instead!
     */
    final class DoNotUpdateExistingTargetNamespace
    {
        public static function run()
        {
            return 5;
        }
    }

}

namespace Acme\Bar {

    final class DoNotUpdateExistingTargetNamespace
    {
        public static function run()
        {
            return 5;
        }
    }
}

?>
