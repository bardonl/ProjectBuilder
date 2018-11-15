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
            //Get the json file which contains the most used frameworks
            $frameworks = json_decode(file_get_contents(realpath('./app/Frameworks.json')),true)['frameworks'];
            
            //Show the available frameworks and let the user decide which one they want to use
            $this->info('Available frameworks:');
            
            //The amount of entries returned doesn't match the keys, -1 will fix that issue
            for($i = 0; $i <= count($frameworks) -1; $i++)
            {
                $this->info($i . '. ' .$frameworks[$i]['name']);
            }
            
            $config['selectedFramework'] = $this->ask('Please typ the number of the framework you want to use:');
            
            /**Check if the input is a number. If all is well the user will be informed of which framework they chose. Now it will run the command immediately
             * but this will be changed => after all the configuration options the project will be build as a whole rather than in small portions at a time, this allows for reconfiguration
             */
            switch($config['selectedFramework']):
                case !is_numeric($config['selectedFramework']):
                    $this->info('You have to use the numbers to select a framework! You also can\'t select more than one');
                    die;
                case $config['selectedFramework'] > count($frameworks):
                    $this->info('The selected framework does not exist! Please choose a different one.');
                    die;
                case  $config['selectedFramework'] <= count($frameworks):
                    $this->info('You have selected: ' . $frameworks[$config['selectedFramework']]['name']);
                    break;
                default:
                    $this->info('Whoops something went wrong! Try again!');
                    die;
            endswitch;
            
            if ($frameworks[$config['selectedFramework']]['name'] === 'other')
            {
                $frameworkLink = $this->ask("Please specify the download link or repository (framework/framework, if supported by composer)");
            }
            
            /**
             * Zend why don't you use a projectname argument in your composer command?!?!
             * Reeeeee
             */
            if ($frameworks[$config['selectedFramework']]['name'] === 'Zend')
            {
                $this->buildStructure($config);
                exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo']);
            }
            elseif ($frameworks[$config['selectedFramework']]['name'] === 'other') {
                $frameworkLocation = $this->ask("Please specify the download link, repository or the path on your local machine");
                
                $this->locateFramework($frameworkLocation);
            } else {
                //The command that runs composer to create a project with the chosen framework, this will be dynamic because some frameworks don't use a repository as their main distribution source
                exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo'] . ' ' . $config['projectRootPath']);
            }
            
//            switch ($frameworks[$config['selectedFramework']]['name']):
//                case $frameworks[$config['selectedFramework']]['name'] === 'other':
//                    $frameworkLink = $this->ask("Please specify the download link or repository (framework/framework, if supported by composer)");
//                    break;
//                case $frameworks[$config['selectedFramework']]['name'] === 'Zend':
//                    $this->buildStructure($config);
//                    exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo']);
//                    break;
//                case :
            
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
    
    function buildStructure($config)
    {
        if($this->dependencyInjectionManager()->getFileFolderGeneratorService()->buildFolderStructure($config))
        {
            $config['newProjectName'] = $this->ask('Project already exists! Choose a new project name!');
            $this->call('build:project', ['projectName' => $config['newProjectName']]);
            die;
        }
    }
    
    public function locateFramework($frameworkLocation)
    {
        
    }
}
