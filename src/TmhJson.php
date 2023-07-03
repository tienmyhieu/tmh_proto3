<?php

class TmhJson
{
    public function attribute($path, $file)
    {
        return $this->load($path, $file);
    }

    public function catalog($path, $file)
    {
        return $this->load($path, $file);
    }

    public function component($path, $file)
    {
        return $this->load($path, $file);
    }

    public function locale($path, $file)
    {
        return $this->load($path, $file);
    }

    public function routes($path)
    {
        return $this->toKeyed($this->load($path, 'routes'), 'entity');
    }

    public function template($path, $file)
    {
        return $this->load($path, $file);
    }

    private function load($path, $file, $associative=true)
    {
        //echo (new Exception())->getTraceAsString() . PHP_EOL;
        $contents = '[]';
        if ($this->exists($path .  $file . '.json')) {
            //echo "<pre>" . 'reading ' . $path . $file . PHP_EOL . "</pre>";
            $contents = file_get_contents($path .  $file . '.json');
        } else {
            //echo "<pre>" . 'not reading ' . $path . $file . PHP_EOL . "</pre>";
        }
        return json_decode($contents, $associative);
    }

    private function exists($url): bool
    {
        return (false !== @file_get_contents($url, 0, null, 0, 1));
    }

    private function toKeyed($entities, $key)
    {
        $transformed = [];
        foreach ($entities as $entity) {
            if (is_array($entity) && array_key_exists($key, $entity)) {
                $transformed[$entity[$key]] = $entity;
            }
        }
        return $transformed ?: $entities;
    }
}