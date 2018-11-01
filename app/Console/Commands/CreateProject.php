<?php

namespace App\Console\Commands;

use App\Traits\DependencyInjectionManagerTrait;
use Illuminate\Console\Command;

class CreateProject extends Command
{
    
    use DependencyInjectionManagerTrait;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'build:project {projectName}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return mixed
     */
    public function handle()
    {
        /**
         * This is an array where all the user preferences are stored
         * Such as:
         * - Project name
         * - Which framework(s) (Like laravel, symfony)
         * - Front end configurations (Bootstrap, Boilerplate, JS/JQ)
         * - Back end configurations (Config file, db credentials if needed, a couple of default controllers. When using laravel/symfony these questions wont be asked)
         * - Want to upload to github after creation
         */
        $config = [];
        
        $config['projectName'] = $this->argument('projectName');
        $config['projectRootPath'] = ROOTPATH . '/' . $config['projectName'];
        
        $this->checkValidInput($config);
        
        $this->buildStructure($config);
        
    }
    
    /**
     * Another failsafe just in case if the projectName argument is empty, if so, prompt the user to use a new project name
     *
     * @param $config
     *
     * @return bool
     */
    function checkValidInput($config)
    {
        if ($config['projectName'] === '' || empty($config['projectName']))
        {
            $config['newProjectName'] = $this->ask('You have to choose a project name! Choose your project name.');
            $this->call('build:project', ['projectName' => $config['newProjectName']]);
            die;
        }
    }
    
    /**
     * Parse the config array to the structure builder service, when this is done the build function checks if the project already exists.
     * If the project already exists the user is prompted to choose a new project name
     *
     * @param $config
     *
     * @return void
     */
    function buildStructure($config)
    {
        if($this->dependencyInjectionManager()->getFileFolderGeneratorService()->buildFolderStructure($config))
        {
            $config['newProjectName'] = $this->ask('Project already exists! Choose a new project name!');
            $this->call('build:project', ['projectName' => $config['newProjectName']]);
            die;
        }
    }
}
