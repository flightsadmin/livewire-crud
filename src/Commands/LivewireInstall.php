<?php

namespace Flightsadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

class LivewireInstall extends Command
{
	protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];
	
    protected $signature = 'crud:install';
    protected $description = 'Install Livewire CRUD Generator, compile and publish it\'s assets';

    public function handle()
    {
        $this->filesystem = new Filesystem;
		(new Filesystem)->ensureDirectoryExists(app_path('Http/Livewire'));
		(new Filesystem)->ensureDirectoryExists(app_path('Http/Controllers'));
		(new Filesystem)->ensureDirectoryExists(app_path('Models'));
		(new Filesystem)->ensureDirectoryExists(resource_path('views/livewire'));
		
        if ($this->confirm('This will delete compiled assets in public folder. It will Re-Compile this. Do you want to proceed?') == 'yes') {        
			if ($this->confirm('Do you want to scaffold Authentication files? Only skip if you have authentication system on your App') == 'yes') {
					Artisan::call('ui:auth', [], $this->getOutput());
			}
		$routeFile = base_path('routes/web.php');
		$string = file_get_contents($routeFile);
        if (!str_contains($string, '//Route Hooks - Do not delete//')) {
			file_put_contents($routeFile, "\n//Route Hooks - Do not delete//", FILE_APPEND);
		}
        $deleteFiles = [
            'resources/sass',
            'resources/css',
            'resources/js',
            'public/css',
            'public/js',
            'public/fonts',
        ];

        foreach ($deleteFiles as $deleteFile) {
            if ($this->filesystem->exists($deleteFile)) {
                $this->filesystem->delete($deleteFile);
                $this->filesystem->deleteDirectory($deleteFile);
                $this->warn('Deleted file: <info>' . $deleteFile . '</info>');
            }
        }
		
        $this->stubDir = __DIR__ . '/../../resources/install';
        $this->generateFiles();
		
		$this->line('');
		$this->warn('Running: <info>npm install && npm run dev</info> Please wait...');
		$this->line('');

		exec('npm install && npm run dev');

        $this->info('Installation Complete, few seconds please, let us optimize your site');
        $this->warn('');
        $this->warn('Removing Dumped node_modules files. Please wait...');
		
		tap(new Filesystem, function ($npm) {
            $npm->deleteDirectory(base_path('node_modules'));
            $npm->delete(base_path('yarn.lock'));
            $npm->delete(base_path('package-lock.json'));
        });
        $this->info('node_modules files Removed');
        $this->info('');
        $this->warn('All set, run <info>php artisan crud:generate {table-name}</info> to build your CRUD');		
	  }
		else $this->warn('Installation Aborted, No file was changed');
    }
	
	public function generateFiles()
    {
        foreach ($this->filesystem->allFiles($this->stubDir, true) as $file) {
            $filePath = $this->replace(Str::replaceLast('.stub', '', $file->getRelativePathname()));
            $fileDir = $this->replace($file->getRelativePath());

            if ($fileDir) {
                $this->filesystem->ensureDirectoryExists($fileDir);
            }

            $this->filesystem->put($filePath, $this->replace($file->getContents()));

            $this->warn('Generated file: <info>' . $filePath . '</info>');
        }
    }

    private function replace($content)
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}