<?php

namespace App;

class Validator
{
    public function validate(array $user)
    {
        $errors = [];
        if ($user['name'] == '') {
            $errors['name'] = "Can't be blank";
        }
        return $errors;
    }
}
