<?php
    function sanitize($dbCon, $input, $type="string"){
        $status = true;

        $input = mysqli_real_escape_string($dbCon, $input);

        switch($type){
            case "string":
                $sanitizedValue = filter_var($input, FILTER_SANITIZE_STRING);
                break;
            case "int":
                $status = filter_var($input, FILTER_VALIDATE_INT) === 0 || !filter_var($input, FILTER_VALIDATE_INT) === false;
                $sanitizedValue = $input;
                break;
            case "decimal":
                $status = filter_var($input, FILTER_VALIDATE_FLOAT) === 0 || !filter_var($input, FILTER_VALIDATE_FLOAT) === false;
                $sanitizedValue = $input;
                break;
        }

        return array($sanitizedValue, $input);
        
    }
?>