<?php

namespace App;

class Repository
{
    public function getData($file)
    {
        return json_decode(file_get_contents($file), true);
    }

    public function saveData(array $user, $file)
    {
        if ($user['name'] === '') {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }
        $users = $this->getData($file);
        $user['id'] = count($users) + 1;
        $users[] = $user;
        file_put_contents($file, json_encode($users));
    }
}
