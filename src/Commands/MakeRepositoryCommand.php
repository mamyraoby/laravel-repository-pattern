<?php

namespace Raoby\Commands;

use Illuminate\Support\Str;
use Raoby\Traits\CommandTrait;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Contracts\Console\PromptsForMissingInput;

class MakeRepositoryCommand extends Command implements PromptsForMissingInput
{
    use CommandTrait;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature;

    /**
     * Abstract class suffix
     *
     * @var string
     */
    protected string $abstractSuffix;


    /**
     * Abstract folder
     *
     * @var string
     */
    protected string $abstractFolder;

    /**
     * Abstract root namespace
     *
     * @var string
     */
    protected string $abstractRootNamespace;

    /**
     * Concrete class suffix
     *
     * @var string
     */
    protected string $concreteSuffix;

    /**
     * Concrete folder
     *
     * @var string
     */
    protected string $concreteFolder;

    /**
     * Concrete root namespace
     *
     * @var string
     */
    protected string $concreteRootNamespace;

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate repository for an Eloquent Model based';

    public function __construct()
    {
        $this->abstractSuffix        = config('raoby.repository.abstract.suffix');
        $this->abstractFolder        = $this->getFolderAbsolutePath(config('raoby.repository.abstract.path'));
        $this->abstractRootNamespace = config('raoby.repository.abstract.namespace');

        $this->concreteSuffix        = config('raoby.repository.concrete.suffix');
        $this->concreteFolder        = $this->getFolderAbsolutePath(config('raoby.repository.concrete.path'));
        $this->concreteRootNamespace = config('raoby.repository.concrete.namespace');

        $this->signature = config('raoby.command.repository.make') . '
            {name : The name of the repository. Must be suffixed by "' . $this->concreteSuffix .'"}';

        parent::__construct();
    }

    /**
     * Prompt for missing input arguments using the returned questions.
     *
     * @return array
     */
    protected function promptForMissingArgumentsUsing()
    {
        return [
            'name' => [
                'What is the name of the repository? Must be suffixed by "' . $this->concreteSuffix .'"',
                'E.g.: UserRepository'
            ],
        ];
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $inputName = $this->argument('name');

        if (!$this->checkNameSuffix($inputName, $this->concreteSuffix)) {
            $this->error('Repository name must be suffixed by "' . $this->concreteSuffix .'"');
            return Command::FAILURE;
        }

        $abstractFile      = $this->getFilename($this->abstractFolder, $inputName, $this->concreteSuffix, $this->abstractSuffix);
        $abstractClassName = $this->getClassName($this->concreteSuffix, $this->abstractSuffix, $inputName);
        $abstractNamespace = $this->getNamespace($this->abstractRootNamespace, $inputName);
        $abstractContent   = $this->loadAndReplaceStub('raoby.repository.contract', [
            '{{ NAMESPACE }}' => $abstractNamespace,
            '{{ CLASSNAME }}' => $abstractClassName,
        ]);

        $concreteFile      = $this->getFilename($this->concreteFolder, $inputName, $this->concreteSuffix, $this->concreteSuffix);
        $concreteClassName = $this->getClassName($this->concreteSuffix, $this->concreteSuffix, $inputName);
        $concreteNamespace = $this->getNamespace($this->concreteRootNamespace, $inputName);
        $concreteContent   = $this->loadAndReplaceStub('raoby.repository.concrete', [
            '{{ NAMESPACE }}' => $concreteNamespace,
            '{{ CLASSNAME }}' => $concreteClassName,
            '{{ CONTRACT_NAMESPACE }}' => $abstractNamespace,
            '{{ CONTRACT_CLASSNAME }}' => $abstractClassName,
        ]);;

        $writable = !(File::exists($abstractFile) && File::exists($concreteFile));

        if ($writable) {

            $this->touchFile($abstractFile);
            $this->touchFile($concreteFile);

            File::put($abstractFile, $abstractContent);
            File::put($concreteFile, $concreteContent);

            $this->info('Repository created successfully.');
            return Command::SUCCESS;
        }

        $this->error('The specified folder in the configuration file is not writtable.');
        return Command::FAILURE;
    }
}
