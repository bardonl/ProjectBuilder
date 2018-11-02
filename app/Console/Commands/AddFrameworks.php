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
            $frameworks = json_decode(file_get_contents(realpath('./app/Frameworks.json')),true)['frameworks'];
            
            $this->info('Available frameworks:');
            
            for($i = 1; $i <= count($frameworks) -1; $i++)
            {
                $this->info($i . '. ' .$frameworks[$i]['name']);
            }
            
            $config['selectedFramework'] = $this->ask('Please typ the number of the framework you want to use:');
            
            switch($config['selectedFramework']):
                case !is_numeric($config['selectedFramework']):
                    $this->info('You have to use the numbers to select a framework! You also can\'t select more than one');
                    die;
                case $config['selectedFramework'] > count($frameworks) -1:
                    $this->info('The selected framework does not exist! Please choose a different one.');
                    die;
                case  $config['selectedFramework'] <= count($frameworks) -1:
                    $this->info('You have selected: ' . $frameworks[$config['selectedFramework'] -1]['name']);
                    break;
                default:
                    $this->info('Whoops something went wrong! Try again!');
                    die;
            endswitch;
            
            exec('composer create-project ' . $frameworks[$config['selectedFramework']-1]['repo'] . ' ' . $config['projectRootPath']);
        } else {
            if($this->dependencyInjectionManager()->getFileFolderGeneratorService()->buildFolderStructure($config))
            {
                $config['newProjectName'] = $this->ask('Project already exists! Choose a new project name!');
                $this->call('build:project', ['projectName' => $config['newProjectName']]);
                die;
            }
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
    
    public function multipleChoice($question, $choices)
    {

    }
}
