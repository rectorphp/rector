<?php

namespace T1 {
    class A {
        /**
         * @return $this
         */
        public function itself()
        {
            return $this;
        }
    }
}

namespace T2 {
    use T1\A;

    class B {
        public function test(): A
        {
            return (new A)->itself();
        }
    }
}
?>
-----
<?php

namespace T1 {
    class A {
        /**
         * @return $this
         */
        public function itself(): self
        {
            return $this;
        }
    }
}

namespace T2 {
    use T1\A;

    class B {
        public function test(): A
        {
            return (new A)->itself();
        }
    }
}
?>
