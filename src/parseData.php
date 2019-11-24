<?php

namespace HP\Parser;

function csvToArray($delimeter, $enclosure, $fileSourse)
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

/* function saveArrayAsJson($array)
{
    file_put_contents(__DIR__ . '/../data/characters.json', json_encode($array));
}

$characters = csvToArray(';', '"', __DIR__ . '/../data/characters.csv');
$requiredKeys = ['name','wand','patronus'];
$result = [];
foreach ($characters as $index => $character) {
    foreach ($character as $key => $value) {
        if (in_array($key, $requiredKeys)) {
            $result[$index][$key] = $value;
        } elseif ($key = 'id') {
            $result[$index][$key] = uniqid();
        }
    }
}

saveArrayAsJson($result); */
