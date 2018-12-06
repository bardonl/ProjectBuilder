<?php

namespace App\Utility;

use App\Services\ArrayifyService;
use Faker\Provider\File;
use Illuminate\Filesystem\Filesystem;
use App\Services\CheckFilesFolders;
use App\Services\FileFolderGeneratorService;
use Symfony\Component\Yaml\Tests\A;

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
     * @var ArrayifyService
     */
    protected $arrayifyService;
    
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
    
    /**
     * @return FileFolderGeneratorService
     */
    function getFileFolderGeneratorService()
    {
        if (($this->fileFolderGeneratorService instanceof FileFolderGeneratorService) === false)
        {
            $this->fileFolderGeneratorService = new FileFolderGeneratorService();
        }
        
        return $this->fileFolderGeneratorService;
    }
    
    function getArrayifyService()
    {
        if (($this->arrayifyService instanceof ArrayifyService) === false)
        {
            $this->arrayifyService = new ArrayifyService();
        }
        
        return $this->arrayifyService;
    }
}