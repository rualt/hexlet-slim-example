<?php

namespace App;

class Validator
{
    public function validate(array $user, array $originalUser = [])
    {
        $errors = [];

        foreach ($user as $key => $value) {
            if ($value == '') {
                $errors[$key] = "can't be blank";
            }
        }

        $intersection = array_intersect_key($originalUser, $user);
        if ($intersection === $user) {
            $errors['no change'] = "You haven't changed anything";
        }
        
        return $errors;
    }
}
