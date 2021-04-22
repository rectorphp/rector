<?php

namespace Vendor {
    class SomeParent
    {
        public function enqueueAt($at)
        {
        }
    }
}

namespace App {
    use Vendor\SomeParent;
    
    final class Child extends SomeParent
    {
        /**
         * @param DateTime|int $at
         */
        public function enqueueAt($at)
        {
        }
    }
}
?>

