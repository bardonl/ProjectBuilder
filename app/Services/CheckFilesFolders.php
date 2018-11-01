<?php
/**
 * Created by PhpStorm.
 * User: bart_
 * Date: 1-11-2018
 * Time: 14:48
 */

namespace App\Services;

use App\Traits\DependencyInjectionManagerTrait;

class CheckFilesFolders
{
    
    use DependencyInjectionManagerTrait;
    
    /**
     * @var $path
     *
     * @return boolean (true/false)
     */
    function doesExist($path)
    {
        if ($this->dependencyInjectionManager()->getFileSystem()->exists($path))
        {
            $response = true;
        }
        else
        {
            $response = false;
        }
        
        return $response;
    }
}