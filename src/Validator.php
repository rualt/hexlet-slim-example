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

        if (empty($user['gender'])) {
            $errors['gender'] = "Can't be blank";
        }

        return $errors;
    }
}
