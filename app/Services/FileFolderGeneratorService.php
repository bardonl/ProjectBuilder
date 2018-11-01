<?php

namespace App\Services;

use App\Traits\DependencyInjectionManagerTrait;

class FileFolderGeneratorService
{
    use DependencyInjectionManagerTrait;
    
    /**
     * Check if the project already exists, if so, return true and prompt the user to aks for a new name
     * If not create the root folder of the project
     * Check if the root folder has been created (This is not necessary, but it is good to get some feedback if it went right or wrong
     *
     * @param $config
     * @return bool
     */
    function buildFolderStructure($config)
    {
        if($this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($config['projectRootPath']))
        {
            return true;
        }
        
        $this->dependencyInjectionManager()->getFileSystem()->makeDirectory( $config['projectRootPath'], 755, true);
    
        if($this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($config['projectRootPath']))
        {
            echo 'Root created successfully';
        }
    }
}