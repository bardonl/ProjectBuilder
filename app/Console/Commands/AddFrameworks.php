<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Traits\DependencyInjectionManagerTrait;
use Mockery\Exception;
use Symfony\Component\Finder\Iterator\RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

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
     * @todo error handling
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
            
            for($i = 0; $i <= count($frameworks) -1; $i++)
            {
                $this->info($i + 1 . '. ' .$frameworks[$i]['name']);
            }
            
            $config['selectedFramework'] = $this->ask('Please typ the number of the framework you want to use:');
            
            /**Check if the input is a number. If all is well the user will be informed of which framework they can select. Now it will run the command immediately
             * but this will be changed => after all the configuration options the project will be build as a whole rather than in small portions at a time, this allows for reconfiguration
             */
            switch($config['selectedFramework']):
                case !is_numeric($config['selectedFramework'] - 1):
                    $this->info('You have to use the numbers to select a framework! You also can\'t select more than one');
                    break;
                case $config['selectedFramework'] - 1 > count($frameworks):
                    $this->info('The selected framework does not exist! Please choose a different one.');
                    break;
                case  $config['selectedFramework'] - 1 <= count($frameworks):
                    $this->info('You have selected: ' . $frameworks[$config['selectedFramework'] - 1]['name']);
                    break;
                default:
                    $this->info('Whoops something went wrong! Try again!');
                    break;
            endswitch;
            
            //A zend project is created differently than other frameworks, that's why it has it's own check and command
            if ($frameworks[$config['selectedFramework'] -1]['name'] === 'Zend')
            {
                $this->buildStructure($config);
                exec('composer create-project ' . $frameworks[$config['selectedFramework']]['repo']);
            }
            elseif ($frameworks[$config['selectedFramework'] -1]['name'] === 'Other') {
                $config['frameworkLocation'] = $this->ask("Please specify the download link, repository or the path on your local machine");
                
                $this->locateFramework($config);
            } else {
                //The command that runs composer to create a project with the chosen framework
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
    
    /**
     * @param $frameworkLocation
     * @param $config
     */
    public function locateFramework($config)
    {
        $found = false;
        
        while ($found === false){
            
            if (filter_var($config['frameworkLocation'], FILTER_VALIDATE_URL)) {
                $this->info('Valid URL!, Checking if the link is working');
                list($status) = get_headers($config['frameworkLocation']);
                if (!strpos($status, '404'))
                {
                    $this->info('URL is working, trying to download the Framework!');
                    $this->downloadFramework($config);
                } else {
                    $this->info('The link is returning ' . $status . '. Check if the URL is correct and the website isn\'t down!');
                }
                die;
            } elseif ($this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($config['frameworkLocation'])) {
                $this->info('Framework found!');
                $found = true;
                $this->buildStructure($config);
                $this->copyFramework($config['frameworkLocation'], $config['projectRootPath']);
            } elseif (!$this->dependencyInjectionManager()->getCheckFilesFolders()->doesExist($config['frameworkLocation']))
            {
                $config['frameworkLocation'] = $this->ask('Whoops, your framework hasn\'t been found on your local machine, please make sure the location is correct!');
            }
        }
    }
    
    /**
     * @param $source
     * @param $destination
     *
     * Would love to recreate this with laravel function and the DependencyInjectionManager
     * But it will fall flat on its face when you do that.
     * Need to look into that in the future
     */
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
    
    /**
     * @param $frameworkLocation
     *
     * Download the framework from the internet using cURL to a zip file called TempFramework.zip (Everytime your download a new framework this file will be flushed beforehand)
     */
    function downloadFramework($config)
    {
        try{
            $this->flushTempFolders();
            $zipFile = 'TempFramework.zip';
            $zipResource = fopen($zipFile, 'w+');
            $ch = curl_init();
        
            if ($ch === false) {
                throw new Exception('failed to initialize');
            }
            
            curl_setopt($ch, CURLOPT_URL, $config['frameworkLocation']);
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
    
            $this->openTempZip($config);
            
        } catch (Exception $e) {
            trigger_error(sprintf (
                'curl failed with error #%d: %s',
                $e->getCode(), $e->getMessage()),
                E_USER_ERROR);
        }
    }
    
    /**
     * open the TempFramework.zip to extract the downloaded framework and place it in the TempFramework folder for later usage
     */
    function openTempZip($config)
    {
        $zip = new \ZipArchive();
        $fwResources =  $zip->open('TempFramework.zip');
        
        if($fwResources === TRUE) {
            $zip->extractTo('TempFramework');
            $zip->close();
            
            $dirs = array_filter(glob('TempFramework/*'), 'is_dir');
            
            $this->copyFramework(ROOTPATH . '\ProjectBuilder\\' . $dirs[0], $config['projectRootPath']);
        } else {
            $this->info('Uh oh. It seems like something went wrong while opening the temporary zip file.');
        }
    }
    
    /**
     * Empty all the Temp folders, this allows for easier code when copying the framework to the project folder
     */
    function flushTempFolders()
    {
        $zip = new \ZipArchive();
        $fwRecources = $zip->open('TempFramework.zip');
    
        if ($fwRecources === true) {
            for ($i = 0; $i <= $zip->numFiles; $i++) {
                $zip->deleteIndex($i);
            }
        }
        
        $dir = ROOTPATH . '\ProjectBuilder\TempFramework';
        
        if (is_dir($dir)) {
            $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
            $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
    
            foreach($files as $file) {
                if ($file->isDir()){
                    @rmdir($file->getRealPath());
                } else {
                    @unlink($file->getRealPath());
                }
            }
    
            @rmdir($dir);
        }
        
    }
}
