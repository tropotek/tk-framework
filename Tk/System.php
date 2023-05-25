<?php
namespace Tk;

use Tk\Traits\ConfigTrait;
use Tk\Traits\FactoryTrait;
use Tk\Traits\RegistryTrait;
use Tk\Traits\SingletonTrait;

/**
 * The System object will contain all system information methods
 * and any methods to set the system state, setTimeZone(), SetLogPath(), etc.
 *
 * Extend this class in your application to add methods relating
 * to your local App system
 *
 *
 * @author Tropotek <http://www.tropotek.com/>
 */
class System
{
    use SingletonTrait;
    use ConfigTrait;
    use FactoryTrait;
    use RegistryTrait;


    protected function __construct() {  }


    /**
     * Return the root path to the site.
     */
    public function discoverBasePath(): string
    {
        return rtrim(dirname(__DIR__, 4), DIRECTORY_SEPARATOR);
    }

    /**
     * Attempt to locate .htaccess and find a RewriteBase parameter to use
     */
    public function discoverBaseUrl(): string
    {
        $path = '';
        $htaccessFile = $this->discoverBasePath() . '/.htaccess';
        if (is_file($htaccessFile)) {
            $htaccess = file_get_contents($htaccessFile);
            if ($htaccess && preg_match('/\s+RewriteBase (\/.*)\s+/i', $htaccess, $regs)) {
                $path = $regs[1];
            }
        }
        return rtrim($path, '/');
    }

    /**
     * Create a full filepath to a resource using the relative path
     * Prepend the system file path to any relative path given.
     *
     * This method will strip the trailing slash.
     * If no DIRECTORY_SEPARATOR is at the beginning of the $path one will be prepended
     *
     */
    public function makePath(string $path, string $prependPath = ''): string
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);
        $path = str_replace($this->getConfig()->getBasePath(), '', $path); // Prevent recurring
        return $this->getConfig()->getBasePath() . $path;
    }

    /**
     * Create a full path URL from a relative path
     *
     * This method will strip the trailing slash.
     * If a full URL is supplied only the path is returned
     *
     */
    public function makeUrl(string $path): string
    {
        $path = rtrim($path, '/');
        $path = parse_url($path, \PHP_URL_PATH);
        $path = str_replace($this->getConfig()->getbaseUrl(), '', $path); // Prevent recurring
        return $this->getConfig()->getbaseUrl() . $path;
    }

    /**
     * Check if the user requested a cache refresh using <Ctrl>+<Shift>+R
     */
    public function isRefreshCacheRequest(): bool
    {
        $headers = $this->getFactory()->getRequest()->headers;
        if ($headers->get('Pragma') == 'no-cache' || $headers->get('Cache-Control') == 'no-cache')
            return true;
        return false;
    }

    /**
     * Get the current script running time in seconds
     */
    public function scriptDuration(): string
    {
        return (string)(microtime(true) - $this->getConfig()->get('script.start.time'));
    }

    /**
     * Test if the request is run from a Command Line Interface (CLI)
     */
    public function isCli(): bool
    {
        return (substr(php_sapi_name(), 0, 3) == 'cli');
    }

    /**
     * Get the composer.json as an array
     */
    public function getComposerJson(): array
    {
        static $composer = null;
        if (!$composer) {
            $composer = [];
            if (is_file($this->getConfig()->getBasePath() . '/composer.json')) {
                $composer = json_decode(file_get_contents($this->getConfig()->getBasePath() . '/composer.json'), true);
            }
        }
        return $composer;
    }

    /**
     * Get the version found in the version file (if any)
     * Returns "1.0" if no version file found
     */
    public function getVersion(): string
    {
        static $version = null;
        if (!$version) {
            $version = '1.0';
            if (is_file($this->getConfig()->getBasePath() . '/version')) {
                $version = file_get_contents($this->getConfig()->getBasePath() . '/version');
            }
        }
        return $version;
    }

}