<?php

function () {
    return new class {
            public function execute()
            {
            }
    };
};

?>
-----
<?php

function () {
    class AnonymousFor_NotInfunctionNoNamespace
    {
        public function execute()
        {
        }
    }
    return new AnonymousFor_NotInfunctionNoNamespace();
};

?>