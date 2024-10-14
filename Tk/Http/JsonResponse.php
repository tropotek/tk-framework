<?php

namespace Tk\Http;

use Tk\Exception;

/**
 * Response represents an HTTP response in JSON format.
 *
 * Note that this class does not force the returned JSON content to be an
 * object. It is however recommended that you do return an object as it
 * protects yourself against XSSI and JSON-JavaScript Hijacking.
 *
 * @see https://github.com/OWASP/CheatSheetSeries/blob/master/cheatsheets/AJAX_Security_Cheat_Sheet.md#always-return-json-with-an-object-on-the-outside
 */
class JsonResponse extends Response
{

    // Encode <, >, ', &, and " characters in the JSON, making it also safe to be embedded into HTML.
    // 15 === JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT
    public const int DEFAULT_ENCODING_OPTIONS = 15;

    protected int    $encodingOptions = self::DEFAULT_ENCODING_OPTIONS;
    protected string $data;

    /**
     * @param bool $json If the data is already a JSON string
     */
    public function __construct(mixed $data = null, int $status = 200, array $headers = [], bool $json = false)
    {
        parent::__construct('', $status, $headers);

        if ($json && !\is_string($data) && !is_numeric($data) && !\is_callable([$data, '__toString'])) {
            throw new Exception(sprintf('"%s": If $json is set to true, argument $data must be a string or object implementing __toString(), "%s" given.', __METHOD__, gettype($data)));
        }

        $data ??= [];

        $json ? $this->setJson($data) : $this->setData($data);
    }

    /**
     * Sets a raw string containing a JSON document to be sent.
     */
    public function setJson(string $json): static
    {
        $this->data = $json;

        return $this->update();
    }

    /**
     * Sets the data to be sent as JSON.
     */
    public function setData(mixed $data = []): static
    {
        try {
            $data = json_encode($data, $this->encodingOptions);
        } catch (\Exception $e) {
            if ('Exception' === $e::class && str_starts_with($e->getMessage(), 'Failed calling ')) {
                throw $e->getPrevious() ?: $e;
            }
            throw $e;
        }

        if (\JSON_THROW_ON_ERROR & $this->encodingOptions) {
            return $this->setJson($data);
        }

        if (\JSON_ERROR_NONE !== json_last_error()) {
            throw new \InvalidArgumentException(json_last_error_msg());
        }

        return $this->setJson($data);
    }

    /**
     * Updates the content and headers according to the JSON data and callback.
     */
    protected function update(): static
    {
        // Only set the header when there is none or when it equals 'text/javascript' (from a previous update with callback)
        // in order to not overwrite a custom definition.
        if (!isset($this->headers['Content-Type']) || 'text/javascript' === $this->headers['Content-Type']) {
            $this->headers['Content-Type'] = 'application/json';
        }
        return $this->setContent($this->data);
    }

    /**
     * Returns options used while encoding data to JSON.
     */
    public function getEncodingOptions(): int
    {
        return $this->encodingOptions;
    }

    /**
     * Sets options used while encoding data to JSON.
     */
    public function setEncodingOptions(int $encodingOptions): static
    {
        $this->encodingOptions = $encodingOptions;

        return $this->setData(json_decode($this->data));
    }


}