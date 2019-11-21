<?php

namespace App;

class Repository
{
    public function getData($file)
    {
        return json_decode(file_get_contents($file), true);
    }

    /* public function find($id, $file)
    {
        $users = $this->getData($file);
        foreach ($users as $user) {
            if ($user['id'] === $id) {
                return $user
            }
        }
    } */

    public function destroy($id, $file)
    {
        $users = $this->getData($file);
        $result = [];
        foreach ($users as $index => $user) {
            if ($user['id'] == $id) {
                unset($users[$index]);
                $result = array_values($users);
                file_put_contents($file, json_encode($result));
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
            $item['id'] = count($users) + 1;
            $users[] = $item;
            file_put_contents($file, json_encode($users));
        } else {
            foreach ($users as $index => $user) {
                foreach ($user as $key => $value) {
                    if ($value === $item['id']) {
                        $users[$index]['name'] = $item['name'];
                        file_put_contents($file, json_encode($users));
                        return $item['id'];
                    }
                }
            }
        }
    }
}
