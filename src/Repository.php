<?php

namespace App;

class Repository
{
    public function getData($file)
    {
        return json_decode(file_get_contents($file), true);
    }

    public function delete($id, $file)
    {
        $users = $this->getData($file);
        foreach ($users as $index => $user) {
            if ($user['id'] == $id) {
                unset($users[$index]);
                file_put_contents($file, json_encode(array_values($users)));
            }
        }
    }

    public function saveData(array $item, $file)
    {
        if ($item['name'] === '') {
            $json = json_encode($item);
            throw new \Exception("Wrong data: {$json}");
        }

        $users = $this->getData($file);

        if (!isset($item['id'])) {
            $item['id'] = uniqid();
            $users[] = $item;
            file_put_contents($file, json_encode($users));
        } else {
            foreach ($users as $index => $user) {
                foreach ($user as $key => $value) {
                    if ($user['id'] === $item['id']) {
                        $users[$index] = $item;
                        file_put_contents($file, json_encode($users));
                        return $item['id'];
                    }
                }
            }
        }
    }
}
