<?php

return strpos($docToken->getContent(), "\n") === false;
return strpos($docToken->getContent(), "\n") !== false;
if (strpos($docToken->getContent(), "\n") !== false) {
    return true;
}
return 5;
