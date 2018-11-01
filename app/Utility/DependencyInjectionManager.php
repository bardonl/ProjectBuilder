<?php

namespace App\Utility;

use Faker\Provider\File;
use Illuminate\Filesystem\Filesystem;
use App\Services\CheckFilesFolders;
use App\Services\FileFolderGeneratorService;

class DependencyInjectionManager
{
    
    /**
     * @var Filesystem
     */
    protected $fileSystem;
    
    /**
     * @var CheckFilesFolders
     */
    protected $checkFilesFolders;
    
    /**
     * @var FileFolderGeneratorService
     */
    protected $fileFolderGeneratorService;
    
    /**
     * @return Filesystem
     */
    function getFileSystem()
    {
        if (($this->fileSystem instanceof Filesystem) === false)
        {
            $this->fileSystem = new Filesystem();
        }
        
        return $this->fileSystem;
    }
    
    /**
     * @return CheckFilesFolders
     */
    function getCheckFilesFolders()
    {
        if (($this->checkFilesFolders instanceof CheckFilesFolders) === false)
        {
            $this->checkFilesFolders = new CheckFilesFolders();
        }
        
        return $this->checkFilesFolders;
    }
    
    function getFileFolderGeneratorService()
    {
        if (($this->fileFolderGeneratorService instanceof FileFolderGeneratorService) === false)
        {
            $this->fileFolderGeneratorService = new FileFolderGeneratorService();
        }
        
        return $this->fileFolderGeneratorService;
    }
}