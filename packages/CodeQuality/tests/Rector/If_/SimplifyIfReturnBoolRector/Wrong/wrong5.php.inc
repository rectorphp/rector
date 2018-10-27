<?php

if (!strpos($docToken->getContent(), "\n")) {
    return false;
}

return true;
