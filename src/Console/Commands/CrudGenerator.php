<?php

namespace Hossam\Licht\Console\Commands;

use Hossam\Licht\Generators\controllerFullstackGenerator;
use Hossam\Licht\Generators\ResourceGenerator;
use Hossam\Licht\Generators\ControllerGenerator;
use Hossam\Licht\Generators\MigrationGenerator;
use Hossam\Licht\Generators\ModelGenerator;
use Hossam\Licht\Generators\RequestsGenerator;
use Hossam\Licht\Generators\ViewGenerator;
use Illuminate\Console\Command;
use Symfony\Component\Console\Question\ChoiceQuestion;
use Illuminate\Support\Str;
use Hossam\Licht\Traits\Helper;

class CrudGenerator extends Command
{
    use Helper;
    protected $signature = 'licht {model?}';
    protected $description = 'Generate CRUD operations for a model';
    protected $migrationName;

    public function handle()
    {
        $modelName = $this->getModelName();

        $fields = $this->gatherFields();

        $this->displayModelFields($fields);

        $this->generateCrudComponents($modelName, $fields);

        $this->displayGeneratedFiles($modelName);
    }
    protected function getModelName()
    {
        $modelName = $this->argument('model');

        if (!$modelName) {
            $modelName = $this->ask('Enter the model name');
            $modelName = $this->wordCase($modelName, 'ModelName');
            if (!preg_match('/^[A-Z][a-zA-Z]*$/', $modelName)) {
                $this->error('Invalid model name. Model names should contain only letters.');
                $this->getModelName();
            }
        } else {
            $modelName = $this->wordCase($modelName, 'ModelName');
            if (!preg_match('/^[A-Z][a-zA-Z]*$/', $modelName)) {
                $this->error('Invalid model name. Model names should contain only letters.');
                $this->getModelName();
            }
        }

        return $modelName;
    }


    protected function gatherFields()
    {
        $fields = [];
        $askForFields = true;

        while ($askForFields) {
            $fieldType = $this->askFieldType();
            $fieldName = $this->ask('Enter field name', 'name');

            if (!preg_match('/^[a-zA-Z_][a-zA-Z0-9_]*$/', $fieldName)) {
                $this->error('Invalid field name. Field names should start with a letter or underscore and contain only letters, numbers, and underscores.');
                continue;
            }

            $fields[$fieldName] = $fieldType;

            $this->displayModelFields($fields);

            if (!$this->confirm('Add more fields?', true)) {
                $askForFields = false;
            }
        }

        return $fields;
    }

    protected function displayModelFields($fields)
    {
        $this->info("\nModel Fields:");

        $this->table(
            ['Field Name', 'Field Type'],
            collect($fields)->map(function ($type, $field) {
                return [$field, $type];
            })->toArray()
        );
    }

    protected function askFieldType()
    {
        $question = new ChoiceQuestion(
            'Choose field type',
            [
                'string',
                'integer',
                'text',
                'foreignId',
                'image',
                'file',
                'date',
                'datetime',
                'json'
            ],
            0
        );
        return $this->choice($question->getQuestion(), $question->getChoices(), 0);
    }
    // ...

    protected function generateCrudComponents($modelName, $fields)
    {
        $this->info("Generating CRUD components for {$modelName}...");

        $requestsDirectory = app_path('Http/Requests');
        if (!file_exists($requestsDirectory)) {
            mkdir($requestsDirectory, 0755, true);
        }

        $resourcesDirectory = app_path('Http/Resources');
        if (!file_exists($resourcesDirectory)) {
            mkdir($resourcesDirectory, 0755, true);
        }

        $generators = [
            'Model' => new ModelGenerator,
            'Requests' => new RequestsGenerator,
            'Resource' => new ResourceGenerator,
            'Controller' => new controllerFullstackGenerator,
            'Migration' => new MigrationGenerator,
            'View' => new ViewGenerator,
        ];

        $totalSteps = count($generators);
        $bar = $this->output->createProgressBar($totalSteps);

        $bar->setBarWidth(50);
        $bar->setBarCharacter('<comment>=</comment>');
        $bar->setEmptyBarCharacter('-');
        $bar->setProgressCharacter('<info>></info>');
        $bar->start();

        foreach ($generators as $component => $generator) {
            $bar->setMessage("Generating $component...");
            if ($component == 'Migration') {
                $this->migrationName = $generator->create($modelName, $fields);
            } else {
                $generator->create($modelName, $fields);
            }
            $bar->advance();
            usleep(30000);
        }

        $bar->setMessage("All components generated!");
        $bar->finish();
        sleep(1);
    }

    //


    protected function displayGeneratedFiles($modelName)
    {
        $pluralModelName = Str::plural($modelName);
        $fileName = strtolower($pluralModelName) . '.blade.php';
        $this->info("\nGenerated files for {$modelName}:");
        $generators = [
            'Model' => app_path("Models/{$modelName}.php"),
            'Store Request' => app_path("Http/Requests/Store{$modelName}Request.php"),
            'Update Request' => app_path("Http/Requests/Update{$modelName}Request.php"),
            'Resource' => app_path("Http/Resources/{$modelName}Resource.php"),
            'Controller' => app_path("Http/Controllers/{$modelName}Controller.php"),
            'Migration' => database_path("migrations/{$this->migrationName}"),
            'View' =>  resource_path("views/{$fileName}"),
        ];
        $this->table(
            ['Component', 'Path'],
            collect($generators)->map(function ($path, $component) {
                return [$component, $path];
            })->toArray(),
            'box'
        );
    }

    protected function getMigrationFileName($modelName)
    {
        $timestamp = now()->format('Y_m_d_His');
        return "{$timestamp}_create_" . Str::snake(Str::plural($modelName)) . "_table.php";
    }
}
