<h1>Project Builder</h1>

<h4>Note: this is still WIP, down here you can read what you can eventually expect from this console application.</h4>

<p>Expect that the readme may change over time, some functionalities might change, disappear or be added to the Project Builder.</p>

<h3>Requirements:</h3>
<ul>
<li><a href="https://getcomposer.org/download/">Composer</a> (Preferably installed globally rather than in the project itself because the application can install frameworks for you if so desired)</li>
<li><a href="https://laravel.com/docs/5.7/installation">Laravel</a></li>
</ul>

<p>This application will help you to set up your default files for any new webapplication</p>

<p>This console application will ask for a few configurations which are need to build a default webapplication to your preferences.</p>
<p>This consists of:</p>
<ul>
<li>Project name</li>
<li>Frameworks needed (Such as Symfony or Laravel)</li>
<li>Front end configuration
    <ul>
    <li>Bootstrap</li>
    <li>Which Bootstrap theme</li>
    <li>Javascript/Jquery (This will automaticly add the file links to your headers, you will also be given the chance to select if you want to have the files locally or want to use the CDN)</li>
    <li>Use HTML boilerplate (This is recommended to use, the less you have to configure, the less likely it is that errors will sneak in to the webapplication)</li>
    <li>Create pages with file extensions the user has specified</li>
    <li>Use "Simple to use" flexbox classes (such as: fd-r = flex-direction:row)</li>
    </ul>
</li>
<li>Back end configuration
    <ul>
    <li>Config file</li>
    <li>Database class, and if the user so desires fill in the credentials to connect to the DB</li>
    <li>Controller (As much as you like to configure before hand)</li>
    </ul>
</li>
</ul>

<h3> How do you use it? </h3>
<p> Open your CLI and navigate to the place where you have put the Project Builder </p>
<p> ```php artisan build:project ProjectName``` </p>
<p> Once this command is called it will check if the project name is valid, has it been used by another project.
Once the check has been complete the application will create the project root folder with the specified name within the same directory as the builder itself. (This might change so you can specify where you want to place your project.)</p>
