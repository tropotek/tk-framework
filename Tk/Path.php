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
        if (empty($path)) throw new Exception("path must contain a value");
        if ($path[0] != '/') throw new Exception("path must start with a directory separator");
        if ($prefix[0] != '/') throw new Exception("prefix must start with a directory separator");

        $this->path = rtrim($path, DIRECTORY_SEPARATOR);
        $this->prefix = rtrim($prefix, DIRECTORY_SEPARATOR);
    }

    /**
     * Create a path from a relative path using the system site root as the base
     */
    public static function create(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        if (str_starts_with($path, Config::getBasePath())) {
            $path = substr($path, strlen(Config::getBasePath()));
        }
        return new self($path, Config::getBasePath());
    }

    /**
     * Create a data path from a relative path using the system data path as the base
     */
    public static function createDataPath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $dataPath = Config::getBasePath() . Config::getDataPath();
        if (str_starts_with($path, $dataPath)) {
            $path = substr($path, strlen($dataPath));
        }
        return new self($path, $dataPath);
    }

    /**
     * Create a private data path, the private folder is unprocessable from the www
     */
    public static function createPrivatePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $privatePath = Config::getBasePath() . Config::getDataPath() . '/private';
        if (str_starts_with($path, $privatePath)) {
            $path = substr($path, strlen($privatePath));
        }
        return new self($path, $privatePath);
    }

    /**
     * Create a path to a cache file or directory
     */
    public static function createCachePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $cachePath = Config::getBasePath() . Config::getCachePath();
        if (str_starts_with($path, $cachePath)) {
            $path = substr($path, strlen($cachePath));
        }
        return new self($path, $cachePath);
    }

    /**
     * Create a path for a temporary file or directory
     */
    public static function createTempPath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $tempPath = Config::getBasePath() . Config::getTempPath();
        if (str_starts_with($path, $tempPath)) {
            $path = substr($path, strlen($tempPath));
        }
        return new self($path, $tempPath);
    }

    /**
     * Create a path to the site HTML template file or directory
     */
    public static function createTemplatePath(string|Path $path = ''): self
    {
        if ($path instanceof Path) return clone $path;
        $templatePath = Config::getBasePath() . Config::getTemplatePath();
        if (str_starts_with($path, $templatePath)) {
            $path = substr($path, strlen($templatePath));
        }
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