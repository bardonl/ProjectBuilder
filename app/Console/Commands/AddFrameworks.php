<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\DependencyInjectionManagerTrait;
use Mockery\Exception;

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
     * Test
     */
    public function handle()
    {
        $config = $this->dependencyInjectionManager()->getArrayifyService()->arrayify($this->argument('config'));
    
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
                $this->info($i + 1 . '. ' .$frameworks[$i]['name']);
            }
            
            $config['selectedFramework'] = $this->ask('Please typ the number of the framework you want to use:');
            
            /**Check if the input is a number. If all is well the user will be informed of which framework they chose. Now it will run the command immediately
             * but this will be changed => after all the configuration options the project will be build as a whole rather than in small portions at a time, this allows for reconfiguration
             */
            switch($config['selectedFramework']):
                case !is_numeric($config['selectedFramework'] - 1):
                    $this->info('You have to use the numbers to select a framework! You also can\'t select more than one');
                    die;
                case $config['selectedFramework'] - 1 > count($frameworks):
                    $this->info('The selected framework does not exist! Please choose a different one.');
                    die;
                case  $config['selectedFramework'] - 1 <= count($frameworks):
                    $this->info('You have selected: ' . $frameworks[$config['selectedFramework'] - 1]['name']);
                    break;
                default:
                    $this->info('Whoops something went wrong! Try again!');
                    die;
            endswitch;
            
            /**
             * Zend why don't you use a projectname argument in your composer command?!?!
             * Reeeeee
             */
            if ($frameworks[$config['selectedFramework'] -1]['name'] === 'Zend')
            {
                $this->buildStructure($config);
                exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo']);
            }
            elseif ($frameworks[$config['selectedFramework'] -1]['name'] === 'Other') {
                $frameworkLocation = $this->ask("Please specify the download link, repository or the path on your local machine");
                
                $this->locateFramework($frameworkLocation, $config);
            } else {
                //The command that runs composer to create a project with the chosen framework, this will be dynamic because some frameworks don't use a repository as their main distribution source
                exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo'] . ' ' . $config['projectRootPath']);
            }
            
        } elseif ($config['frameworksNeeded'] === false) {
            if($this->dependencyInjectionManager()->getFileFolderGeneratorService()->buildFolderStructure($config))
            {
                $config['newProjectName'] = $this->ask('Project already exists! Choose a new project name!');
                $this->call('build:project', ['projectName' => $config['newProjectName']]);
                die;
            }
        } else {
            $this->info('You have to choose either Yes of No!');
            die;
        }
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
    
    public function locateFramework($frameworkLocation, $config)
    {
    
        $found = false;
        
        while ($found === false){
            
            if (filter_var($frameworkLocation, FILTER_VALIDATE_URL)) {
                $this->info('Valid URL!, Checking if the link is working');
                
                list($status) = get_headers($frameworkLocation);
                
                if (!strpos($status, '404'))
                {
                    $this->info('URL is working, trying to download the Framework!');
                    $this->downloadFramework($frameworkLocation);
                    
                } else {
                    $this->info('The link is returning ' . $status . '. Check if the URL is correct and the website isn\'t down!');
                }
                
                var_dump($status);
                
                die;
            } elseif ($this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($frameworkLocation)) {
                $this->info('Framework found!');
                $found = true;
                $this->buildStructure($config);
                $this->copyFramework($frameworkLocation, $config['projectRootPath']);
            } elseif (!$this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($frameworkLocation))
            {
                $frameworkLocation = $this->ask('Whoops, your framework hasn\'t been found on your local machine, please make sure the location is correct!');
            }
        }
    }
    
    function copyFramework($source, $destination) {
        $dir = opendir($source);
        @mkdir($destination);
        
        if (!$this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($destination)) {
            $this->dependencyInjectionManager()->getFileFolderGeneratorService()->buildFolderStructure($destination);
        }
        
        while(false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($source . '/' . $file) ) {
                    $this->copyFramework($source . '/' . $file,$destination . '/' . $file);
                }
                else {
                    copy($source . '/' . $file,$destination . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
    
    function downloadFramework($frameworkLocation)
    {
        try{
            $zipFile = 'TempFramework.zip';
            $zipResource = fopen($zipFile, 'w+');
        
            $ch = curl_init();
        
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
        
            curl_setopt($ch, CURLOPT_URL, $frameworkLocation);
            curl_setopt($ch, CURLOPT_FAILONERROR, true);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_AUTOREFERER, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, false);
            curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_FILE, $zipResource);
            
            $contents = curl_exec($ch);
        
            if ($contents === false) {
                throw new Exception(curl_error($ch), curl_errno($ch));
            }
        
            curl_close($ch);
            
            $this->info(filesize($zipFile) > 0? true : false);
        
        } catch (Exception $e) {
            trigger_error(sprintf (
                'curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
    }
}
