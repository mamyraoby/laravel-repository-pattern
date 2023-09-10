<?php

namespace Raoby\Providers;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\ServiceProvider;
use Raoby\Traits\CommandTrait;

class AutoBoundServiceProvider extends ServiceProvider
{
    use CommandTrait;

    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        $this->bootAutoBound(
            abstractFolder: $this->getFolderAbsolutePath(config('raoby.repository.abstract.path')),
            abstractNamespace: config('raoby.repository.abstract.namespace'),
            abstractSuffix: config('raoby.repository.abstract.suffix'),
            concreteNamespace: config('raoby.repository.concrete.namespace'),
            concreteSuffix: config('raoby.repository.concrete.suffix')
        );

        $this->registerRepositoryModels();
    }


    /**
     * Boot auto bound
     *
     * @param string $abstractFolder
     * @param string $abstractNamespace
     * @param string $concreteNamespace
     * @param string $abstractSuffix
     * @param string $concreteSuffix
     * @return void
     */
    private function bootAutoBound(string $abstractFolder, string $abstractNamespace, string $abstractSuffix, string $concreteNamespace, string $concreteSuffix)
    {
        $abstractFiles = File::exists($abstractFolder)
            ? File::allFiles($abstractFolder)
            : [];

        foreach ($abstractFiles as $abstractFile) {
            $extension    = '.' . $abstractFile->getExtension();
            $relativePath = $abstractFile->getRelativePathname();

            $abstractClassName = Str::replace($extension, '', $relativePath);

            $abstract = $abstractNamespace . $this->replaceSlash($abstractClassName, '\\');
            $concrete = Str::replaceLast($abstractSuffix, $concreteSuffix, Str::replaceFirst($abstractNamespace, $concreteNamespace, $abstract));

            if ((interface_exists($abstract) || class_exists($abstract)) && class_exists($concrete)) {
                $this->app->bind($abstract, $concrete);
            }
        }
    }

    /**
     * Register repository models
     *
     * @return void
     */
    public function registerRepositoryModels(): void {
        $repositoryFolder = $this->getFolderAbsolutePath(config('raoby.repository.concrete.path'));

        $repositoryNamespace = config('raoby.repository.concrete.namespace');
        $modelNamespace = config('raoby.models.namespace', 'App\\Models\\');

        $repositorySuffix = config('raoby.repository.concrete.suffix');
        $modelSuffix      = '';

        $repositoryFiles =File::exists($repositoryFolder)
            ? File::allFiles($repositoryFolder)
            : [];

        foreach ($repositoryFiles as $repositoryFile) {
            $extension    = '.' . $repositoryFile->getExtension();
            $relativePath = $repositoryFile->getRelativePathname();

            $repositoryClassName = Str::replace($extension, '', $relativePath);
            $modelClassName      = Str::replace($repositorySuffix . $extension, $modelSuffix, $relativePath);

            $repository = $repositoryNamespace . $this->replaceSlash($repositoryClassName, '\\');
            $model      = $modelNamespace . $this->replaceSlash($modelClassName, '\\');

            if (!class_exists($model)) {
                $model = $modelNamespace . Str::afterLast($modelClassName, '/');
            }

            if (class_exists($repository) && class_exists($model)) {
                $this->app
                    ->when($repository)
                    ->needs(\Illuminate\Database\Eloquent\Model::class)
                    ->give($model);
            }
        }
    }
}
