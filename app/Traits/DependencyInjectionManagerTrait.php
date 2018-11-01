<?php

namespace App\Traits;

use App\Utility\DependencyInjectionManager;

trait DependencyInjectionManagerTrait
{
    protected $dependencyInjectionManager;
    
    public function dependencyInjectionManager()
    {
        if (($this->dependencyInjectionManager instanceof DependencyInjectionManager) === false)
        {
            $this->dependencyInjectionManager = new DependencyInjectionManager();
        }
        
        return $this->dependencyInjectionManager;
    }
}