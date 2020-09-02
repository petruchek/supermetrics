<?php

// basic autoload function following PSR-4 guidelines
// based on https://www.php.net/manual/en/language.namespaces.rationale.php

spl_autoload_register(
    function ($className) {
        $fileName = '';
        $namespace = '';

        // Sets the include path as the "src" directory
        $includePath = dirname(__FILE__).DIRECTORY_SEPARATOR.'src';

        if (false !== ($lastNsPos = strripos($className, '\\'))) {
            $namespace = substr($className, 0, $lastNsPos);
            $className = substr($className, $lastNsPos + 1);
            $fileName = str_replace('\\', DIRECTORY_SEPARATOR, $namespace).DIRECTORY_SEPARATOR;
        }

        $fileName .= str_replace('_', DIRECTORY_SEPARATOR, $className) . '.php';
        $fullFileName = $includePath . DIRECTORY_SEPARATOR . $fileName;
       
        if (file_exists($fullFileName)) {
            include_once $fullFileName;
        }

        return; //can't do much here, move to the next autoload
    }
);
