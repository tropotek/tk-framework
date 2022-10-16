<?php
namespace Tk\Db\Util;


use Symfony\Component\HttpFoundation\Request;
use Tk\Traits\SystemTrait;
use Tk\Uri;

/**
 * @author Tropotek <http://www.tropotek.com/>
 */
class Mirror
{
    use SystemTrait;

    public function doDefault(Request $request)
    {
//        if (!$this->getConfig()->isDebug()) {
//            throw new \Tk\Exception('Only available for live sites.', 500);
//        }

        if (strtolower($request->getScheme()) != Uri::SCHEME_HTTP_SSL) {
            throw new \Tk\Exception('Only available over SSL connections.', 500);
        }
        if ($this->getConfig()->get('db.mirror.secret') !== $request->request->get('secret')) {
            throw new \Tk\Exception('Invalid access key.', 500);
        }

        if ($request->request->get('action') == 'db') {
            $this->doDbBackup($request);
        } elseif ($request->request->get('action') == 'data') {
            return $this->doDataBackup($request);
        }

        return 'Invalid access request.';
    }

    public function doDbBackup(Request $request)
    {

        $dbBackup = new SqlBackup($this->getFactory()->getDb());
        $exclude = [$this->getConfig()->get('session.db_table')];

        $path = $this->getConfig()->getTempPath() . '/db_mirror.sql';
        $dbBackup->save($path, ['exclude' => $exclude]);

        if (is_file($path . '.gz'))
            @unlink($path . '.gz');

        $command = sprintf('gzip ' . $path);
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

    protected function _fileOutput($filename)
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

    public function doDataBackup(Request $request)
    {
        return 'Not implemented yet!';
    }

}
