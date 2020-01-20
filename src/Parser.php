<?php

namespace App;

class Parser
{
    public function decodeCsvToArray($delimeter, $enclosure, $fileSourse)
    {
        $characters = [];
        if (($handle = fopen($fileSourse, "r")) !== false) {
            while (($data = fgetcsv($handle, 1000, $delimeter, $enclosure)) !== false) {
                $characters[] = $data;
            }
            fclose($handle);
        }

        $keys = [];
        foreach ($characters[0] as $key) {
            $keys[] = $key;
        }

        $result = [];
        for ($i = 1; $i < count($characters); $i++) {
            $result[] = array_combine($keys, $characters[$i]);
        }

        return $result;
    }

    public function saveArrayAsJson($array)
    {
        file_put_contents(__DIR__ . '/../data/characters.json', json_encode($array));
    }
}
