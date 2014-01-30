<?php

/**
 * Parse JSON File
 *
 * @param string $path
 */
function parse_json_file($path, $withPath = false)
{
    if(!$withPath)
    {
        $file = str_finish(base_path(), '/').$path;
    }
    else
    {
        $file = $path;
    }

    try
    {
        return App::make('files')->get($file);
    }
    catch (\Illuminate\Filesystem\FileNotFoundException $e)
    {
        // @ToDo - better Error Management
        var_dump($e);
    }
}

/**
 * Return Component Path
 */
function component_path()
{
    $path = str_finish(base_path(), '/');
    if(!str_contains(Config::get('components::location'), './'))
    {
        $path .= Config::get('components::location');
    }

    $path .= Config::get('components::folderName');

    return str_finish($path, '/');
}