<?php

namespace Flightsadmin\LivewireCrud\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use File;

class LivewireCrudGenerator extends LivewireGeneratorCommand
{
	
	protected $filesystem;
    protected $stubDir;
    protected $argument;
    private $replaces = [];

    protected $signature = 'crud:generate {name : Table name}';

    protected $description = 'Generate Livewire Component and CRUD operations';

    /**
     * Execute the console command.
     * @return bool|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function handle()
    {
        $this->table = $this->getNameInput();

        // If table not exist in DB return
        if (!$this->tableExists()) {
            $this->error("`{$this->table}` table not exist");

            return false;
        }

        // Build the class name from table name
        $this->name = $this->_buildClassName();

        // Generate the crud
           $this->buildModel()
				->buildViews();
		
		//Updating Routes
        $this->filesystem = new Filesystem;
        $this->argument = $this->getNameInput();
        $routeFile = base_path('routes/web.php');
        $routeContents = $this->filesystem->get($routeFile);
        $routeItemStub = "\tRoute::view('" . 	$this->getNameInput() . "', 'livewire." . $this->getNameInput() . ".index')->middleware('auth');";
		$routeItemHook = '//Route Hooks - Do not delete//';

        if (!Str::contains($routeContents, $routeItemStub)) {
            $newContents = str_replace($routeItemHook, $routeItemHook . PHP_EOL . $routeItemStub, $routeContents);
            $this->filesystem->put($routeFile, $newContents);
            $this->warn('Route inserted: <info>' . $routeFile . '</info>');
        }		
		
		//Updating Nav Bar
        $layoutFile = 'resources/views/layouts/app.blade.php';
        $layoutContents = $this->filesystem->get($layoutFile);
        $navItemStub = "\t\t\t\t\t\t<li class=\"nav-item\">
                            <a href=\"{{ url('/".$this->getNameInput()."') }}\" class=\"nav-link\"><i class=\"fab fa-laravel text-info\"></i> ". ucfirst($this->getNameInput()) ."</a> 
                        </li>";
        $navItemHook = '<!--Nav Bar Hooks - Do not delete!!-->';

        if (!Str::contains($layoutContents, $navItemStub)) {
            $newContents = str_replace($navItemHook, $navItemHook . PHP_EOL . $navItemStub, $layoutContents);
            $this->filesystem->put($layoutFile, $newContents);
            $this->warn('Nav link inserted: <info>' . $layoutFile . '</info>');
        }
		
        $this->info('');
        $this->info('Livewire Component & CRUD Generated Successfully.');

        return true;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function buildModel()
    {
        $modelPath = $this->_getModelPath($this->name);
		$livewirePath = $this->_getLivewirePath($this->name);
        $factoryPath = $this->_getFactoryPath($this->name);

        if ($this->files->exists($livewirePath) && $this->ask("Livewire Component ". Str::studly(Str::singular($this->table)) ."Component Already exist. Do you want overwrite (y/n)?", 'y') == 'n') {
            return $this;
        }

        // Make Replacements in Model / Livewire / Migrations / Factories
        $replace = array_merge($this->buildReplacements(), $this->modelReplacements());

        $modelTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Model')
        );
		$factoryTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Factory')
        );
        $livewireTemplate = str_replace(
            array_keys($replace), array_values($replace), $this->getStub('Livewire')
        );
        $this->warn('Creating: <info>Livewire Component...</info>');
        $this->write($livewirePath, $livewireTemplate);
		$this->warn('Creating: <info>Model...</info>');
        $this->write($modelPath, $modelTemplate);
        $this->warn('Creating: <info>Factories, Please edit before running Factory ...</info>');
        $this->write($factoryPath, $factoryTemplate);

        return $this;
    }

    /**
     * @return $this
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \Exception
     */
    protected function buildViews()
    {
        $this->warn('Creating:<info> Views ...</info>');

        $tableHead = "\n";
        $tableBody = "\n";
        $viewRows = "\n";
        $form = "\n";
        $type = null;

        foreach ($this->getFilteredColumns() as $column) {
            $title = Str::title(str_replace('_', ' ', $column));

            $tableHead .= "\t\t\t\t". $this->getHead($title);
            $tableBody .= "\t\t\t\t". $this->getBody($column);
            $form .= $this->getField($title, $column, 'form-field');
			$form .= "\n";
        }
		
		foreach ($this->getColumns() as $values) {
			$type = "text";
            // if (Str::endsWith(($values->Type), ['timestamp', 'date', 'datetime'])) {
                // $type = "date";
            // } 
			// elseif (Str::endsWith(($values->Type), 'int')) {
				// $type = "number";
			// }
			// elseif (Str::startsWith(($values->Type), 'time')) {
				// $type = "time";
			// }
			// elseif (Str::contains(($values->Type), 'text')) {
				// $type = "textarea";
			// }
			// else{
				// $type = "text";
			// }
		}
		
        $replace = array_merge($this->buildReplacements(), [
            '{{tableHeader}}' => $tableHead,
            '{{tableBody}}' => $tableBody,
            '{{viewRows}}' => $viewRows,
            '{{form}}' => $form,
            '{{type}}' => $type,
        ]);

        $this->buildLayout();

        foreach (['view', 'index', 'create', 'update'] as $view) {
            $viewTemplate = str_replace(
                array_keys($replace), array_values($replace), $this->getStub("views/{$view}")
            );

            $this->write($this->_getViewPath($view), $viewTemplate);
        }

        return $this;
    }

    /**
     * Make the class name from table name.
     *
     * @return string
     */
    private function _buildClassName()
    {
        return Str::studly(Str::singular($this->table));
    }
	
	private function replace($content)
    {
        foreach ($this->replaces as $search => $replace) {
            $content = str_replace($search, $replace, $content);
        }

        return $content;
    }
}
