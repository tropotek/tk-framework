<?php
namespace Tk\Db\Util;

use JetBrains\PhpStorm\NoReturn;
use Tk\Config;
use Tk\Uri;
use Tk\Db;

/**
 * @todo The saved sql file should also be encoded with the secret key
 */
class Mirror
{

    public function doDefault(): string
    {
//        if (!$this->getConfig()->isDebug()) {
//            throw new \Tk\Exception('Only available for live sites.', 500);
//        }

        if (strtolower($_SERVER['REQUEST_SCHEME'] ?? 'http') != Uri::SCHEME_HTTP_SSL) {
            throw new \Tk\Exception('Only available over SSL connections.', 500);
        }
        if (!Config::instance()->get('db.mirror.secret', false)) {
            throw new \Tk\Exception('Access Disabled');
        }
        if (Config::instance()->get('db.mirror.secret', null) !== ($_POST['secret'] ?? '')) {
            throw new \Tk\Exception('Invalid access key.', 500);
        }

        $action = trim($_POST['action']);
        if ($action == 'db') {
            $this->doDbBackup();
        } elseif ($action == 'file') {
            $this->doDataBackup();
        }

        return 'Invalid access request.';
    }

    #[NoReturn] public function doDbBackup(): void
    {

        $dbBackup = new SqlBackup(Db::getPdo());
        $exclude = [Config::instance()->get('session.db_table')];

        $path = Config::instance()->getTempPath() . '/db_mirror.sql';
        $dbBackup->save($path, ['exclude' => $exclude]);

        if (is_file($path . '.gz'))
            @unlink($path . '.gz');

        $command = sprintf('gzip %s', $path);
        exec($command, $out, $ret);
        if ($ret != 0) {
            throw new \Tk\Db\Exception(implode("\n", $out));
        }
        $path .= '.gz';

        $public_name = basename($path);
        $filesize = filesize($path);
        header("Content-Disposition: attachment; filename=$public_name;");
        header("Content-Type: application/octet-stream");
        header('Content-Length: '.$filesize);
        $this->_fileOutput($path);

        exit;
    }

    protected function _fileOutput($filename): void
    {
        $filesize = filesize($filename);
        $chunksize = 4096;
        if($filesize > $chunksize) {
            $srcStream = fopen($filename, 'rb');
            $dstStream = fopen('php://output', 'wb');
            $offset = 0;
            while(!feof($srcStream)) {
                $offset += stream_copy_to_stream($srcStream, $dstStream, $chunksize, $offset);
            }
            fclose($dstStream);
            fclose($srcStream);
        } else {
            // stream_copy_to_stream behaves() strange when filesize > chunksize.
            // Seems to never hit the EOF.
            // On the other hand file_get_contents() is not scalable.
            // Therefore, we only use file_get_contents() on small files.
            echo file_get_contents($filename);
        }
    }

    #[NoReturn] public function doDataBackup(): void
    {
//        if (!$this->getConfig()->isDebug()) {
//            throw new \Tk\Exception('Only available for live sites.');
//        }

        $srcFile = Config::instance()->getBasePath() . '/src-'.\Tk\Date::create()->format(\Tk\Date::FORMAT_ISO_DATE).'-data.tgz';
        if (is_file($srcFile)) unlink($srcFile);
        $cmd = sprintf('cd %s && tar zcf %s %s',
            Config::instance()->getBasePath(),
            escapeshellarg(basename($srcFile)),
            basename(Config::instance()->getDataPath())
        );
        //Log::info($cmd);
        system($cmd);

        $public_name = basename($srcFile);
        $filesize = filesize($srcFile);
        header("Content-Disposition: attachment; filename=$public_name;");
        header("Content-Type: application/octet-stream");
        header('Content-Length: '.$filesize);
        $this->_fileOutput($srcFile);
        if (is_file($srcFile)) unlink($srcFile);

        exit;
    }

}
