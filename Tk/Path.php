<?php

namespace Tk;

/**
 * Create a file or directory path from a relative path
 * Use the Config to get the full paths as required
 *
 */
class Path
{

    /**
     * The base relative path
     */
    protected string $path = '';

    /**
     * A prefix path to prepend the final full path
     */
    protected string $prefix = '';


    public function __construct(string $path, string $prefix = '')
    {
        // clean up path
        if (!empty($path)) {
            if ($path[0] != DIRECTORY_SEPARATOR) $path = DIRECTORY_SEPARATOR . $path;
            $path = rtrim($path, DIRECTORY_SEPARATOR);
        }

        if (!empty($prefix)) {
            if ($prefix[0] != DIRECTORY_SEPARATOR) $prefix = DIRECTORY_SEPARATOR . $prefix;
            $prefix = rtrim($prefix, DIRECTORY_SEPARATOR);

            if (str_starts_with($path, $prefix)) {
                $path = substr($path, strlen($prefix));
            }
        }

        // Files/directories starting with a dot should not be accessible generally.
        if (preg_match('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '\./', $path)) {
            throw new Exception("Invalid path value", 0, null, "path: " . $path);
        }
        if (preg_match('/' . preg_quote(DIRECTORY_SEPARATOR, '/') . '\./', $prefix)) {
            throw new Exception("Invalid prefix value", 0, null, "prefix: " . $prefix);
        }

        $this->path = $path;
        $this->prefix = $prefix;
    }

    /**
     * Create a path from a relative path using the supplied prefix if given.
     * The system site root path will be used if null, use '' for no prefix or `new Path()`
     */
    public static function create(string|Path $path = '', ?string $prefix = null): self
    {
        if ($path instanceof Path) return clone $path;
        return new self($path, $prefix ?? Config::getBasePath());
    }

    /**
     * Create a data path from a relative path using the system data path as the base
     */
    public static function createDataPath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $dataPath = Config::getBasePath() . Config::getDataPath();
        return new self($path, $dataPath);
    }

    /**
     * Create a private data path, the private folder is unprocessable from the www
     */
    public static function createPrivatePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $privatePath = Config::getBasePath() . Config::getDataPath() . DIRECTORY_SEPARATOR . 'private';
        return new self($path, $privatePath);
    }

    /**
     * Create a path to a cache file or directory
     */
    public static function createCachePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $cachePath = Config::getBasePath() . Config::getCachePath();
        return new self($path, $cachePath);
    }

    /**
     * Create a path for a temporary file or directory
     */
    public static function createTempPath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $tempPath = Config::getBasePath() . Config::getTempPath();
        return new self($path, $tempPath);
    }

    /**
     * Create a path to the site HTML template file or directory
     */
    public static function createTemplatePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $templatePath = Config::getBasePath() . Config::getTemplatePath();
        return new self($path, $templatePath);
    }


    public function getPath(): string
    {
        return $this->path;
    }

    public function getPrefix(): string
    {
        return $this->prefix;
    }

    public function isFile(): bool
    {
        return is_file($this->toString());
    }

    public function isDir(): bool
    {
        return is_dir($this->toString());
    }

    public function exists(): bool
    {
        return file_exists($this->toString());
    }


    public function toRelativeString(): string
    {
        return $this->getPath();
    }

    public function toString(): string
    {
        return $this->getPrefix() . $this->getPath();
    }

    public function __toString(): string
    {
        return $this->toString();
    }

}