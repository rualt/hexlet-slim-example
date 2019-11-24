<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];

        foreach ($user as $key => $value) {
            if ($value == '') {
                $errors[$key] = "can't be blank";
            }
        }
        
        return $errors;
    }
}
