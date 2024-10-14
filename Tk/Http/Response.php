<?php
namespace Tk\Http;

/**
 * Response represents an HTTP response.
 */
class Response
{
    public const int HTTP_CONTINUE = 100;
    public const int HTTP_SWITCHING_PROTOCOLS = 101;
    public const int HTTP_PROCESSING = 102;            // RFC2518
    public const int HTTP_EARLY_HINTS = 103;           // RFC8297
    public const int HTTP_OK = 200;
    public const int HTTP_CREATED = 201;
    public const int HTTP_ACCEPTED = 202;
    public const int HTTP_NON_AUTHORITATIVE_INFORMATION = 203;
    public const int HTTP_NO_CONTENT = 204;
    public const int HTTP_RESET_CONTENT = 205;
    public const int HTTP_PARTIAL_CONTENT = 206;
    public const int HTTP_MULTI_STATUS = 207;          // RFC4918
    public const int HTTP_ALREADY_REPORTED = 208;      // RFC5842
    public const int HTTP_IM_USED = 226;               // RFC3229
    public const int HTTP_MULTIPLE_CHOICES = 300;
    public const int HTTP_MOVED_PERMANENTLY = 301;
    public const int HTTP_FOUND = 302;
    public const int HTTP_SEE_OTHER = 303;
    public const int HTTP_NOT_MODIFIED = 304;
    public const int HTTP_USE_PROXY = 305;
    public const int HTTP_RESERVED = 306;
    public const int HTTP_TEMPORARY_REDIRECT = 307;
    public const int HTTP_PERMANENTLY_REDIRECT = 308;  // RFC7238
    public const int HTTP_BAD_REQUEST = 400;
    public const int HTTP_UNAUTHORIZED = 401;
    public const int HTTP_PAYMENT_REQUIRED = 402;
    public const int HTTP_FORBIDDEN = 403;
    public const int HTTP_NOT_FOUND = 404;
    public const int HTTP_METHOD_NOT_ALLOWED = 405;
    public const int HTTP_NOT_ACCEPTABLE = 406;
    public const int HTTP_PROXY_AUTHENTICATION_REQUIRED = 407;
    public const int HTTP_REQUEST_TIMEOUT = 408;
    public const int HTTP_CONFLICT = 409;
    public const int HTTP_GONE = 410;
    public const int HTTP_LENGTH_REQUIRED = 411;
    public const int HTTP_PRECONDITION_FAILED = 412;
    public const int HTTP_REQUEST_ENTITY_TOO_LARGE = 413;
    public const int HTTP_REQUEST_URI_TOO_LONG = 414;
    public const int HTTP_UNSUPPORTED_MEDIA_TYPE = 415;
    public const int HTTP_REQUESTED_RANGE_NOT_SATISFIABLE = 416;
    public const int HTTP_EXPECTATION_FAILED = 417;
    public const int HTTP_I_AM_A_TEAPOT = 418;                                               // RFC2324
    public const int HTTP_MISDIRECTED_REQUEST = 421;                                         // RFC7540
    public const int HTTP_UNPROCESSABLE_ENTITY = 422;                                        // RFC4918
    public const int HTTP_LOCKED = 423;                                                      // RFC4918
    public const int HTTP_FAILED_DEPENDENCY = 424;                                           // RFC4918
    public const int HTTP_TOO_EARLY = 425;                                                   // RFC-ietf-httpbis-replay-04
    public const int HTTP_UPGRADE_REQUIRED = 426;                                            // RFC2817
    public const int HTTP_PRECONDITION_REQUIRED = 428;                                       // RFC6585
    public const int HTTP_TOO_MANY_REQUESTS = 429;                                           // RFC6585
    public const int HTTP_REQUEST_HEADER_FIELDS_TOO_LARGE = 431;                             // RFC6585
    public const int HTTP_UNAVAILABLE_FOR_LEGAL_REASONS = 451;                               // RFC7725
    public const int HTTP_INTERNAL_SERVER_ERROR = 500;
    public const int HTTP_NOT_IMPLEMENTED = 501;
    public const int HTTP_BAD_GATEWAY = 502;
    public const int HTTP_SERVICE_UNAVAILABLE = 503;
    public const int HTTP_GATEWAY_TIMEOUT = 504;
    public const int HTTP_VERSION_NOT_SUPPORTED = 505;
    public const int HTTP_VARIANT_ALSO_NEGOTIATES_EXPERIMENTAL = 506;                        // RFC2295
    public const int HTTP_INSUFFICIENT_STORAGE = 507;                                        // RFC4918
    public const int HTTP_LOOP_DETECTED = 508;                                               // RFC5842
    public const int HTTP_NOT_EXTENDED = 510;                                                // RFC2774
    public const int HTTP_NETWORK_AUTHENTICATION_REQUIRED = 511;                             // RFC6585

    /**
     * Status codes translation table.
     *
     * The list of codes is complete according to the
     * {@link https://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml Hypertext Transfer Protocol (HTTP) Status Code Registry}
     * (last updated 2021-10-01).
     *
     * Unless otherwise noted, the status code is defined in RFC2616.
     */
    public static array $statusTexts = [
        100 => 'Continue',
        101 => 'Switching Protocols',
        102 => 'Processing',            // RFC2518
        103 => 'Early Hints',
        200 => 'OK',
        201 => 'Created',
        202 => 'Accepted',
        203 => 'Non-Authoritative Information',
        204 => 'No Content',
        205 => 'Reset Content',
        206 => 'Partial Content',
        207 => 'Multi-Status',          // RFC4918
        208 => 'Already Reported',      // RFC5842
        226 => 'IM Used',               // RFC3229
        300 => 'Multiple Choices',
        301 => 'Moved Permanently',
        302 => 'Found',
        303 => 'See Other',
        304 => 'Not Modified',
        305 => 'Use Proxy',
        307 => 'Temporary Redirect',
        308 => 'Permanent Redirect',    // RFC7238
        400 => 'Bad Request',
        401 => 'Unauthorized',
        402 => 'Payment Required',
        403 => 'Forbidden',
        404 => 'Not Found',
        405 => 'Method Not Allowed',
        406 => 'Not Acceptable',
        407 => 'Proxy Authentication Required',
        408 => 'Request Timeout',
        409 => 'Conflict',
        410 => 'Gone',
        411 => 'Length Required',
        412 => 'Precondition Failed',
        413 => 'Content Too Large',                                           // RFC-ietf-httpbis-semantics
        414 => 'URI Too Long',
        415 => 'Unsupported Media Type',
        416 => 'Range Not Satisfiable',
        417 => 'Expectation Failed',
        418 => 'I\'m a teapot',                                               // RFC2324
        421 => 'Misdirected Request',                                         // RFC7540
        422 => 'Unprocessable Content',                                       // RFC-ietf-httpbis-semantics
        423 => 'Locked',                                                      // RFC4918
        424 => 'Failed Dependency',                                           // RFC4918
        425 => 'Too Early',                                                   // RFC-ietf-httpbis-replay-04
        426 => 'Upgrade Required',                                            // RFC2817
        428 => 'Precondition Required',                                       // RFC6585
        429 => 'Too Many Requests',                                           // RFC6585
        431 => 'Request Header Fields Too Large',                             // RFC6585
        451 => 'Unavailable For Legal Reasons',                               // RFC7725
        500 => 'Internal Server Error',
        501 => 'Not Implemented',
        502 => 'Bad Gateway',
        503 => 'Service Unavailable',
        504 => 'Gateway Timeout',
        505 => 'HTTP Version Not Supported',
        506 => 'Variant Also Negotiates',                                     // RFC2295
        507 => 'Insufficient Storage',                                        // RFC4918
        508 => 'Loop Detected',                                               // RFC5842
        510 => 'Not Extended',                                                // RFC2774
        511 => 'Network Authentication Required',                             // RFC6585
    ];

    public const string DISPOSITION_ATTACHMENT = 'attachment';  // default
    public const string DISPOSITION_INLINE     = 'inline';

    public array $headers = [];

    protected string $content    = '';
    protected string $version    = '';
    protected int    $statusCode = 0;
    protected string $statusText = '';
    protected string $charset    = '';


    public function __construct(?string $content = '', int $status = 200, array $headers = [])
    {
        $this->headers = $headers;
        $this->setContent($content);
        $this->setStatusCode($status);
        $this->setProtocolVersion('1.0');
    }

    /**
     * Prepares the Response before it is sent to the client.
     *
     * This method tweaks the Response to ensure that it is
     * compliant with RFC 2616. Most of the changes are based on
     * the Request that is "associated" with this Response.
     */
    public function prepare(): static
    {
        if ($this->isInformational() || $this->isEmpty()) {
            $this->setContent(null);
            unset($this->headers['Content-Type']);
            unset($this->headers['Content-Length']);
            // prevent PHP from sending the Content-Type header based on default_mimetype
            ini_set('default_mimetype', '');
        } else {

            // Fix Content-Type
            $charset = $this->charset ?: 'UTF-8';
            if (!isset($this->headers['Content-Type'])) {
                $this->headers['Content-Type'] = 'text/html; charset='.$charset;
            } elseif (0 === stripos($this->headers['Content-Type'], 'text/') && false === stripos($this->headers['Content-Type'], 'charset')) {
                // add the charset
                $this->headers['Content-Type'] = $this->headers['Content-Type'].'; charset='.$charset;
            }

            // Fix Content-Length
            if (isset($this->headers['Transfer-Encoding'])) {
                unset($this->headers['Transfer-Encoding']);
            }

            if (!isset($this->headers['Date'])) {
                $this->setDate(new \DateTime());
            }

            if (!isset($this->headers['Cache-Control'])) {
                $this->headers['Cache-Control'] = 'no-store, no-cache, must-revalidate';
            }

            $method = strtoupper($_SERVER['REQUEST_METHOD'] ?? 'GET');
            if ($method == 'HEAD') {
                // cf. RFC2616 14.13
                $length = $this->headers['Content-Length'] ?? 0;
                $this->setContent(null);
                if ($length) {
                    $this->headers['Content-Length'] = $length;
                }
            }
        }

        // Fix protocol
        if ('HTTP/1.0' != ($_SERVER['SERVER_PROTOCOL'] ?? '')) {
            $this->setProtocolVersion('1.1');
        }

        // Check if we need to send extra expire info headers
        if ('1.0' == $this->getProtocolVersion() && str_contains(($this->headers['Cache-Control'] ?? ''), 'no-cache')) {
            $this->headers['pragma']  = 'no-cache';
            $this->headers['expires'] = -1;
        }

        return $this;
    }

    public function sendHeaders(): static
    {
        // headers have already been sent by the developer
        if (headers_sent()) {
            return $this;
        }

        if (!$this->getLastModified()) {
            $this->setLastModified(new \DateTime());
        }

        // headers (without cookies)
        foreach ($this->headers as $name => $value) {
            if (strtolower($name) == 'status') continue;
            header($name.': '.$value, true, $this->statusCode);
        }

        // status
        header(sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText), true, $this->statusCode);

        return $this;
    }

    /**
     * Sends content for the current web response.
     */
    public function sendContent(): static
    {
        echo $this->content;

        return $this;
    }

    /**
     * Sends HTTP headers and content.
     *
     * @param bool $flush Whether output buffers should be flushed
     */
    public function send(bool $flush = true): static
    {
        $this->prepare();
        $this->sendHeaders();
        $this->sendContent();

        if (!$flush) {
            return $this;
        }

        if (\function_exists('fastcgi_finish_request')) {
            fastcgi_finish_request();
        } elseif (!\in_array(\PHP_SAPI, ['cli', 'phpdbg', 'embed'], true)) {
            static::closeOutputBuffers(0, true);
            flush();
        }

        return $this;
    }

    /**
     * Sets the response content.
     */
    public function setContent(?string $content): static
    {
        $this->content = $content ?? '';

        return $this;
    }

    /**
     * Gets the current response content.
     */
    public function getContent(): string|false
    {
        return $this->content;
    }

    /**
     * Sets the HTTP protocol version (1.0 or 1.1).
     */
    final public function setProtocolVersion(string $version): static
    {
        $this->version = $version;

        return $this;
    }

    /**
     * Gets the HTTP protocol version.
     */
    final public function getProtocolVersion(): string
    {
        return $this->version;
    }

    /**
     * Sets the response status code.
     *
     * If the status text is null it will be automatically populated for the known
     * status codes and left empty otherwise.
     */
    final public function setStatusCode(int $code, ?string $text = null): static
    {
        $this->statusCode = $code;
        if ($this->isInvalid()) {
            throw new \InvalidArgumentException(sprintf('The HTTP status code "%s" is not valid.', $code));
        }

        if (null === $text) {
            $this->statusText = self::$statusTexts[$code] ?? 'unknown status';

            return $this;
        }

        $this->statusText = $text;

        return $this;
    }

    /**
     * Retrieves the status code for the current web response.
     */
    final public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Sets the response charset.
     */
    final public function setCharset(string $charset): static
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Retrieves the response charset.
     */
    final public function getCharset(): ?string
    {
        return $this->charset;
    }

    /**
     * Returns the Date header as a DateTime instance.
     */
    final public function getDate(): ?\DateTimeImmutable
    {
        if (!isset($this->headers['Date'])) return null;

        $value = $this->headers['Date'];
        if (false === $date = \DateTimeImmutable::createFromFormat(\DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The "Date" HTTP header is not parseable (%s).', $value));
        }

        return $date;
    }

    /**
     * Sets the Date header.
     */
    final public function setDate(\DateTimeInterface $date): static
    {
        $date = \DateTimeImmutable::createFromInterface($date);
        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers['Date'] = $date->format('D, d M Y H:i:s').' GMT';

        return $this;
    }

    /**
     * Returns the age of the response in seconds.
     */
    final public function getAge(): int
    {
        if (null !== $age = ($this->headers['Age'] ?? null)) {
            return (int)$age;
        }

        return max(time() - (int)$this->getDate()->format('U'), 0);
    }

    /**
     * Returns the value of the Expires header as a DateTime instance.
     */
    final public function getExpires(): ?\DateTimeImmutable
    {
        if (!isset($this->headers['Expires'])) return null;

        $value = $this->headers['Expires'];
        if (false === $date = \DateTimeImmutable::createFromFormat(\DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The "Expires" HTTP header is not parseable (%s).', $value));
        }

        return $date;
    }

    /**
     * Sets the Expires HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     */
    final public function setExpires(?\DateTimeInterface $date = null): static
    {
        if (null === $date) {
            unset($this->headers['Expires']);
            return $this;
        }

        $date = \DateTimeImmutable::createFromInterface($date);
        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers['Expires'] = $date->format('D, d M Y H:i:s').' GMT';

        return $this;
    }


    /**
     * Returns the Last-Modified HTTP header as a DateTime instance.
     */
    final public function getLastModified(): ?\DateTimeImmutable
    {
        if (!isset($this->headers['Last-Modified'])) return null;

        $value = $this->headers['Last-Modified'];
        if (false === $date = \DateTimeImmutable::createFromFormat(\DATE_RFC2822, $value)) {
            throw new \RuntimeException(sprintf('The "Last-Modified" HTTP header is not parseable (%s).', $value));
        }

        return $date;
    }

    /**
     * Sets the Last-Modified HTTP header with a DateTime instance.
     *
     * Passing null as value will remove the header.
     */
    final public function setLastModified(?\DateTimeInterface $date = null): static
    {
        if (null === $date) {
            unset($this->headers['Last-Modified']);
            return $this;
        }

        $date = \DateTimeImmutable::createFromInterface($date);
        $date = $date->setTimezone(new \DateTimeZone('UTC'));
        $this->headers['Last-Modified'] = $date->format('D, d M Y H:i:s').' GMT';

        return $this;
    }

    /**
     * Returns the literal value of the ETag HTTP header.
     */
    final public function getEtag(): ?string
    {
        return $this->headers['ETag'] ?? null;
    }

    /**
     * Sets the ETag value.
     *
     * @param string|null $etag The ETag unique identifier or null to remove the header
     * @param bool        $weak Whether you want a weak ETag or not
     */
    public function setEtag(?string $etag = null, bool $weak = false): static
    {
        if (null === $etag) {
            unset($this->headers['Etag']);
        } else {
            if (!str_starts_with($etag, '"')) {
                $etag = '"'.$etag.'"';
            }

            $this->headers['ETag'] = (true === $weak ? 'W/' : '') . $etag ;
        }

        return $this;
    }

    /**
     * Is response invalid?
     *
     * @see https://www.w3.org/Protocols/rfc2616/rfc2616-sec10.html
     */
    final public function isInvalid(): bool
    {
        return $this->statusCode < 100 || $this->statusCode >= 600;
    }

    /**
     * Is response informative?
     */
    final public function isInformational(): bool
    {
        return $this->statusCode >= 100 && $this->statusCode < 200;
    }

    /**
     * Is response successful?
     */
    final public function isSuccessful(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }

    /**
     * Is the response a redirect?
     */
    final public function isRedirection(): bool
    {
        return $this->statusCode >= 300 && $this->statusCode < 400;
    }

    /**
     * Is there a client error?
     */
    final public function isClientError(): bool
    {
        return $this->statusCode >= 400 && $this->statusCode < 500;
    }

    /**
     * Was there a server side error?
     */
    final public function isServerError(): bool
    {
        return $this->statusCode >= 500 && $this->statusCode < 600;
    }

    /**
     * Is the response OK?
     */
    final public function isOk(): bool
    {
        return 200 === $this->statusCode;
    }

    /**
     * Is the response forbidden?
     */
    final public function isForbidden(): bool
    {
        return 403 === $this->statusCode;
    }

    /**
     * Is the response a not found error?
     */
    final public function isNotFound(): bool
    {
        return 404 === $this->statusCode;
    }

    /**
     * Is the response a redirect of some form?
     */
    final public function isRedirect(?string $location = null): bool
    {
        return \in_array($this->statusCode, [201, 301, 302, 303, 307, 308]) &&
            (null === $location || $location == ($this->headers['Location'] ?? ''));
    }

    /**
     * Is the response empty?
     */
    final public function isEmpty(): bool
    {
        return \in_array($this->statusCode, [204, 304]);
    }

    /**
     * Cleans or flushes output buffers up to target level.
     *
     * Resulting level can be greater than target level if a non-removable buffer has been encountered.
     */
    final public static function closeOutputBuffers(int $targetLevel, bool $flush): void
    {
        $status = ob_get_status(true);
        $level = \count($status);
        $flags = \PHP_OUTPUT_HANDLER_REMOVABLE | ($flush ? \PHP_OUTPUT_HANDLER_FLUSHABLE : \PHP_OUTPUT_HANDLER_CLEANABLE);

        while ($level-- > $targetLevel && ($s = $status[$level]) && (!isset($s['del']) ? !isset($s['flags']) || ($s['flags'] & $flags) === $flags : $s['del'])) {
            if ($flush) {
                ob_end_flush();
            } else {
                ob_end_clean();
            }
        }
    }

    /**
     * Returns the Response as an HTTP string.
     *
     * The string representation of the Response is the same as the
     * one that will be sent to the client only if the prepare() method
     * has been called before.
     */
    public function __toString(): string
    {
        $headers = array_map(fn($k, $v): string => sprintf('%s: %s', $k, $v), array_keys($this->headers), array_values($this->headers));
        return
            sprintf('HTTP/%s %s %s', $this->version, $this->statusCode, $this->statusText) . "\r\n".
            implode("\r\n", $headers) . "\r\n".
            $this->getContent();
    }

    /**
     * Generates an HTTP Content-Disposition field-value.
     *
     * @param string $disposition      One of "inline" or "attachment"
     * @param string $filename         A unicode string
     * @param string $filenameFallback A string containing only ASCII characters that
     *                                 is semantically equivalent to $filename. If the filename is already ASCII,
     *                                 it can be omitted, or just copied from $filename
     * @see RFC 6266
     */
    public static function makeDisposition(string $disposition, string $filename, string $filenameFallback = ''): string
    {
        if (!\in_array($disposition, [self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE])) {
            throw new \InvalidArgumentException(sprintf('The disposition must be either "%s" or "%s".', self::DISPOSITION_ATTACHMENT, self::DISPOSITION_INLINE));
        }

        if ('' === $filenameFallback) {
            $filenameFallback = $filename;
        }

        // filenameFallback is not ASCII.
        if (!preg_match('/^[\x20-\x7e]*$/', $filenameFallback)) {
            throw new \InvalidArgumentException('The filename fallback must only contain ASCII characters.');
        }

        // percent characters aren't safe in fallback.
        if (str_contains($filenameFallback, '%')) {
            throw new \InvalidArgumentException('The filename fallback cannot contain the "%" character.');
        }

        // path separators aren't allowed in either.
        if (str_contains($filename, '/') || str_contains($filename, '\\') || str_contains($filenameFallback, '/') || str_contains($filenameFallback, '\\')) {
            throw new \InvalidArgumentException('The filename and the fallback cannot contain the "/" and "\\" characters.');
        }

        $params = ['filename' => $filenameFallback];
        if ($filename !== $filenameFallback) {
            $params['filename*'] = "utf-8''" . rawurlencode($filename);
        }

        return $disposition . '; ' . self::headerToString($params, ';');
    }

    /**
     * Joins an associative array into a string for use in an HTTP header.
     *
     * The key and value of each entry are joined with '=', and all entries
     * are joined with the specified separator and an additional space (for
     * readability). Values are quoted if necessary.
     *
     * Example:
     *
     *     HeaderUtils::toString(['foo' => 'abc', 'bar' => true, 'baz' => 'a b c'], ',')
     *     // => 'foo=abc, bar, baz="a b c"'
     */
    public static function headerToString(array $assoc, string $separator): string
    {
        $parts = [];
        foreach ($assoc as $name => $value) {
            if (true === $value) {
                $parts[] = $name;
            } else {
                $parts[] = $name.'='.self::headerQuote($value);
            }
        }

        return implode($separator.' ', $parts);
    }

    /**
     * Encodes a string as a quoted string, if necessary.
     *
     * If a string contains characters not allowed by the "token" construct in
     * the HTTP specification, it is backslash-escaped and enclosed in quotes
     * to match the "quoted-string" construct.
     */
    public static function headerQuote(string $s): string
    {
        if (preg_match('/^[a-z0-9!#$%&\'*.^_`|~-]+$/i', $s)) {
            return $s;
        }

        return '"' . addcslashes($s, '"\\"') . '"';
    }

    /**
     * Decodes a quoted string.
     *
     * If passed an unquoted string that matches the "token" construct (as
     * defined in the HTTP specification), it is passed through verbatim.
     */
    public static function headerUnquote(string $s): string
    {
        return preg_replace('/\\\\(.)|"/', '$1', $s);
    }

    /**
     * Splits an HTTP header by one or more separators.
     *
     * Example:
     *     HeaderUtils::split('da, en-gb;q=0.8', ',;')
     *     // => ['da'], ['en-gb', 'q=0.8']]
     *
     * @param string $separators List of characters to split on, ordered by precedence, e.g. ',', ';=', or ',;='
     * @return array Nested array with as many levels as there are characters in $separators
     */
    public static function headerSplit(string $header, string $separators): array
    {
        if ('' === $separators) {
            throw new \InvalidArgumentException('At least one separator must be specified.');
        }

        $quotedSeparators = preg_quote($separators, '/');

        preg_match_all('
            /
                (?!\s)
                    (?:
                        # quoted-string
                        "(?:[^"\\\\]|\\\\.)*(?:"|\\\\|$)
                    |
                        # token
                        [^"'.$quotedSeparators.']+
                    )+
                (?<!\s)
            |
                # separator
                \s*
                (?<separator>['.$quotedSeparators.'])
                \s*
            /x', trim($header), $matches, \PREG_SET_ORDER);

        return self::headerGroupParts($matches, $separators);
    }

    private static function headerGroupParts(array $matches, string $separators, bool $first = true): array
    {
        $separator = $separators[0];
        $separators = substr($separators, 1) ?: '';
        $i = 0;

        if ('' === $separators && !$first) {
            $parts = [''];

            foreach ($matches as $match) {
                if (!$i && isset($match['separator'])) {
                    $i = 1;
                    $parts[1] = '';
                } else {
                    $parts[$i] .= self::headerUnquote($match[0]);
                }
            }

            return $parts;
        }

        $parts = [];
        $partMatches = [];

        foreach ($matches as $match) {
            if (($match['separator'] ?? null) === $separator) {
                ++$i;
            } else {
                $partMatches[$i][] = $match;
            }
        }

        foreach ($partMatches as $matches) {
            if ('' === $separators && '' !== $unquoted = self::headerUnquote($matches[0][0])) {
                $parts[] = $unquoted;
            } elseif ($groupedParts = self::headerGroupParts($matches, $separators, false)) {
                $parts[] = $groupedParts;
            }
        }

        return $parts;
    }

}
