<?php

namespace Tk\Http;

use Tk\Exception;
use Tk\FileUtil;


/**
 * FileResponse represents an HTTP response delivering a file.
 */
class FileResponse extends Response
{
    protected static bool $trustXSendfileTypeHeader = false;

    protected int    $offset    = 0;
    protected int    $maxlen    = -1;
    protected int    $chunkSize = 16 * 1024;
    protected bool   $deleteFileAfterSend = false;
    protected string $filename;

    public function __construct(string $filename, int $status = 200, array $headers = [], ?string $contentDisposition = null, bool $autoLastModified = true)
    {
        parent::__construct(null, $status, $headers);

        $this->setFile($filename, $contentDisposition, $autoLastModified);
    }

    /**
     * Sets the file to stream.
     */
    public function setFile(string $filename, ?string $contentDisposition = null, bool $autoLastModified = true): static
    {
        if (!is_readable($filename)) {
            throw new Exception('File must be readable.');
        }

        $this->filename = $filename;
        $this->setAutoEtag();

        if ($autoLastModified) {
            $this->setAutoLastModified();
        }

        if ($contentDisposition) {
            $this->setContentDisposition($contentDisposition);
        }

        return $this;
    }

    /**
     * Gets the filename
     */
    public function getFilename(): string
    {
        return $this->filename;
    }

    /**
     * Sets the response stream chunk size.
     */
    public function setChunkSize(int $chunkSize): static
    {
        if ($chunkSize < 1 || $chunkSize > \PHP_INT_MAX) {
            throw new \LogicException('The chunk size of a BinaryFileResponse cannot be less than 1 or greater than PHP_INT_MAX.');
        }
        $this->chunkSize = $chunkSize;
        return $this;
    }

    /**
     * Automatically sets the Last-Modified header according the file modification date.
     */
    public function setAutoLastModified(): static
    {
        $this->setLastModified(\DateTimeImmutable::createFromFormat('U', strval(filemtime($this->filename))));
        return $this;
    }

    /**
     * Automatically sets the ETag header according to the checksum of the file.
     */
    public function setAutoEtag(): static
    {
        $this->setEtag(base64_encode(hash_file('sha256', $this->filename, true)));
        return $this;
    }

    /**
     * Sets the Content-Disposition header with the given filename.
     *
     * @param string $disposition      Response::DISPOSITION_INLINE or Response::DISPOSITION_ATTACHMENT
     * @param string $filename         Optionally use this UTF-8 encoded filename instead of the real name of the file
     * @param string $filenameFallback A fallback filename, containing only ASCII characters. Defaults to an automatically encoded filename
     */
    public function setContentDisposition(string $disposition, string $filename = '', string $filenameFallback = ''): static
    {
        if ('' === $filename) {
            $filename = $this->filename;
        }

        if ('' === $filenameFallback && (!preg_match('/^[\x20-\x7e]*$/', $filename) || str_contains($filename, '%'))) {
            $encoding = mb_detect_encoding($filename, null, true) ?: '8bit';

            for ($i = 0, $filenameLength = mb_strlen($filename, $encoding); $i < $filenameLength; ++$i) {
                $char = mb_substr($filename, $i, 1, $encoding);

                if ('%' === $char || \ord($char) < 32 || \ord($char) > 126) {
                    $filenameFallback .= '_';
                } else {
                    $filenameFallback .= $char;
                }
            }
        }

        $dispositionHeader = self::makeDisposition($disposition, $filename, $filenameFallback);
        $this->headers['Content-Disposition'] = $dispositionHeader;

        return $this;
    }

    public function prepare(): static
    {
        if ($this->isInformational() || $this->isEmpty()) {
            parent::prepare();
            $this->maxlen = 0;
            return $this;
        }

        if (!isset($this->headers['Content-Type'])) {
            $this->headers['Content-Type'] = FileUtil::getMimeType($this->filename);
        }

        parent::prepare();
        $this->offset = 0;
        $this->maxlen = -1;

        if (false === $fileSize = filesize($this->filename)) {
            return $this;
        }
        unset($this->headers['Transfer-Encoding']);
        $this->headers['Content-Length'] = $fileSize;

        if (!isset($this->headers['Accept-Ranges'])) {
            $isMethodSafe = in_array($_SERVER['REQUEST_METHOD'], ['GET', 'HEAD', 'OPTIONS', 'TRACE']);
            // Only accept ranges on safe HTTP methods
            $this->headers['Accept-Ranges'] = $isMethodSafe ? 'bytes' : 'none';
        }

        $requestHeaders = getallheaders();
        $method = $_SERVER['REQUEST_METHOD'];

        if (self::$trustXSendfileTypeHeader && isset($requestHeaders['X-Sendfile-Type'])) {
            // Use X-Sendfile, do not send any content.
            $type = $requestHeaders['X-Sendfile-Type'];
            $path = $this->filename;

            if ('x-accel-redirect' === strtolower($type)) {
                // Do X-Accel-Mapping substitutions.
                // @link https://github.com/rack/rack/blob/main/lib/rack/sendfile.rb
                // @link https://mattbrictson.com/blog/accelerated-rails-downloads
                if (!isset($requestHeaders['X-Accel-Mapping'])) {
                    throw new \LogicException('The "X-Accel-Mapping" header must be set when "X-Sendfile-Type" is set to "X-Accel-Redirect".');
                }
                $parts = self::headerSplit($requestHeaders['X-Accel-Mapping'], ',=');
                foreach ($parts as $part) {
                    [$pathPrefix, $location] = $part;
                    if (str_starts_with($path, $pathPrefix)) {
                        $path = $location.substr($path, \strlen($pathPrefix));
                        // Only set X-Accel-Redirect header if a valid URI can be produced
                        // as nginx does not serve arbitrary file paths.
                        $this->headers[$type] = $path;
                        $this->maxlen = 0;
                        break;
                    }
                }
            } else {
                $this->headers[$type] = $path;
                $this->maxlen = 0;
            }
        } elseif (isset($requestHeaders['Range']) && $method == 'GET') {
            // Process the range headers.
            if (!isset($requestHeaders['If-Range']) || $this->hasValidIfRangeHeader($requestHeaders['If-Range'])) {
                $range = $requestHeaders['Range'];

                if (str_starts_with($range, 'bytes=')) {
                    [$start, $end] = explode('-', substr($range, 6), 2) + [1 => 0];

                    $end = ('' === $end) ? $fileSize - 1 : (int) $end;

                    if ('' === $start) {
                        $start = $fileSize - $end;
                        $end = $fileSize - 1;
                    } else {
                        $start = (int) $start;
                    }

                    if ($start <= $end) {
                        $end = min($end, $fileSize - 1);
                        if ($start < 0 || $start > $end) {
                            $this->setStatusCode(416);
                            $this->headers['Content-Range'] = sprintf('bytes */%s', $fileSize);
                        } elseif ($end - $start < $fileSize - 1) {
                            $this->maxlen = $end < $fileSize ? $end - $start + 1 : -1;
                            $this->offset = $start;

                            $this->setStatusCode(206);
                            $this->headers['Content-Range'] = sprintf('bytes %s-%s/%s', $start, $end, $fileSize);
                            $this->headers['Content-Length'] = $end - $start + 1;
                        }
                    }
                }
            }
        }

        if ($method == 'HEAD') {
            $this->maxlen = 0;
        }

        return $this;
    }

    private function hasValidIfRangeHeader(?string $header): bool
    {
        if ($this->getEtag() === $header) {
            return true;
        }

        if (null === $lastModified = $this->getLastModified()) {
            return false;
        }

        return $lastModified->format('D, d M Y H:i:s').' GMT' === $header;
    }

    public function sendContent(): static
    {
        try {
            if (!$this->isSuccessful()) {
                return $this;
            }

            if (0 === $this->maxlen) {
                return $this;
            }

            $out = fopen('php://output', 'w');
            $file = fopen($this->filename, 'r');

            ignore_user_abort(true);

            if (0 !== $this->offset) {
                fseek($file, $this->offset);
            }

            $length = $this->maxlen;
            while ($length && !feof($file)) {
                $read = $length > $this->chunkSize || 0 > $length ? $this->chunkSize : $length;

                if (false === $data = fread($file, $read)) {
                    break;
                }
                while ('' !== $data) {
                    $read = fwrite($out, $data);
                    if (false === $read || connection_aborted()) {
                        break 2;
                    }
                    if (0 < $length) {
                        $length -= $read;
                    }
                    $data = substr($data, $read);
                }
            }

            fclose($out);
            fclose($file);
        } finally {
            if ($this->deleteFileAfterSend && is_file($this->filename)) {
                unlink($this->filename);
            }
        }

        return $this;
    }

    /**
     * @throws \LogicException when the content is not null
     */
    public function setContent(?string $content): static
    {
        if (null !== $content) {
            throw new \LogicException('The content cannot be set on a BinaryFileResponse instance.');
        }
        return $this;
    }

    public function getContent(): string|false
    {
        return false;
    }

    /**
     * Trust X-Sendfile-Type header.
     */
    public static function trustXSendfileTypeHeader():void
    {
        self::$trustXSendfileTypeHeader = true;
    }

    /**
     * If this is set to true, the file will be unlinked after the request is sent
     * Note: If the X-Sendfile header is used, the deleteFileAfterSend setting will not be used.
     */
    public function deleteFileAfterSend(bool $shouldDelete = true): static
    {
        $this->deleteFileAfterSend = $shouldDelete;
        return $this;
    }
}
