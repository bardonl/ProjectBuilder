<?php
/**
 * Created by PhpStorm.
 * User: bart_
 * Date: 23-Nov-18
 * Time: 14:19
 */

namespace App\Services;


class ArrayifyService
{
    /**
     * Convert argument to an array so it is usable within this command
     *
     * @param $configCollection
     * @return mixed
     */
    public function arrayify($configCollection)
    {
        if ($configCollection) {
            foreach ($configCollection as $key => $value) {
                $config[$key] = $value;
            }
        }
        
        return $config;
    }
}