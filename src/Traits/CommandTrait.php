<?php

namespace Raoby\Traits;

use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;

trait CommandTrait
{
    /**
     * Replace slash
     *
     * @param string $path
     * @param string $replacement
     * @return string
     */
    private function replaceSlash(string $path, string $replacement = '/'): string {
        $result = Str::replace('\\', $replacement, Str::replace('/', '\\', $path));
        return $result;
    }

    /**
     * Check suffix
     *
     * @param string $name
     * @return boolean
     */
    public function checkNameSuffix(string $name, string $suffix): bool {
        return Str::endsWith($name, $suffix);
    }

    /**
     * Get classname
     *
     * @param string $nameSuffix
     * @param string $classSuffix
     * @param string $name
     * @return string
     */
    public function getClassName(string $nameSuffix, string $classSuffix, string $name): string
    {
        return Str::replaceLast($nameSuffix, $classSuffix, Str::afterLast($this->replaceSlash($name), '/'));
    }

    /**
     * Get filename absolute path
     *
     * @param string $rootFolder
     * @param string $inputName
     * @param string $inputSuffix
     * @param string $classSuffix
     * @return string
     */
    public function getFilename(string $rootFolder, string $inputName, string $inputSuffix, string $classSuffix): string {
        return $this->replaceSlash($rootFolder . '/' . Str::replaceLast($inputSuffix, $classSuffix, $inputName) . '.php');
    }

    /**
     * Get namespace
     *
     * @param string $rootNamespace
     * @param string $inputName
     * @return string
     */
    public function getNamespace(string $rootNamespace, string $inputName): string
    {
        $className = Str::afterLast($inputName, '/');
        $namespace = $this->replaceSlash($rootNamespace . '\\' . Str::replaceLast($className, '', $inputName, '\\'), '\\');

        return Str::endsWith($namespace, '\\')
            ? Str::replaceLast('\\', '', $namespace)
            : $namespace;
    }

    /**
     * Touch file
     *
     * @param string $filename
     * @return boolean
     */
    public function touchFile(string $filename): bool
    {
        try {
            if (File::exists($filename)) {
                Log::error('The file named: "' . '" is already exists.');
                return false;
            } else {
                if (!File::exists(File::dirname($filename))) {
                    File::makeDirectory(path: File::dirname($filename), recursive: true);
                }
                File::put($filename, '');
                return true;
            }
        } catch (\Throwable $th) {
            Log::error($th->getMessage(), $th->getTrace());
        }
        return false;
    }

    /**
     * Load and replace stub content
     *
     * @param string $name
     * @param array $replacements
     * @return string
     */
    public function loadAndReplaceStub(string $name, array $replacements): string
    {
        $stubFile = __DIR__ . '/../../stubs/' . $name;

        $content = File::get($stubFile);

        foreach($replacements as $search => $replace) {
            $content = Str::replace($search, $replace, $content, true);
        }

        return $content;
    }

    /**
     * Get folder absolute path
     *
     * @param string $path
     * @return string
     */
    public function getFolderAbsolutePath(string $path): string {
        return $this->replaceSlash(base_path($path));
    }
}
