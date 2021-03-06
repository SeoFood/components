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

    if(Config::get('components::type') == 'namespace')
    {
        $path .= Config::get('components::location');
    }
    else
    {
        if(str_contains(Config::get('components::location'), './'))
        {
            $path .= Config::get('components::name');
        }
        else
        {
            $path .= Config::get('components::location');
        }
    }

    return str_finish($path, '/');
}

/**
 * Check if is HMVC Request Helper
 * @return boolean [description]
 */
function isHmvc()
{
    return App::make('components')->isHmvc();
}