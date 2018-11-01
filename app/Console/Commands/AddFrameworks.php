<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\DependencyInjectionManagerTrait;

class AddFrameworks extends Command
{
    
    use DependencyInjectionManagerTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'add:frameworks {config?*}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command to fire the code where frameworks can be added if so desired';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     */
    public function handle()
    {
        $config = $this->arrayify($this->argument('config'));
        
        $config['frameworksNeeded'] = $this->confirm('Do you need any php frameworks?');
        
        if ($config['frameworksNeeded'])
        {
            $config['frameworks'] = array_map('trim', explode(',', $this->ask('Which framework(s) do you want? You can choose multiple frameworks using a comma. Be cautious some frameworks won\'t work together!')));
        }
    }
    
    /**
     * Convert argument to an array so it is usable within this command
     *
     * @param $configCollection
     * @return mixed
     */
    public function arrayify($configCollection)
    {
        if ($configCollection)
        {
            foreach($configCollection as $key => $value)
            {
                $config[$key] = $value;
            }
        }
        
        return $config;
    }
}
