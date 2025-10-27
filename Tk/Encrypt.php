<?php
namespace Tk;

/**
 * An object to handle string encryption based on a secret key
 *
 * Example:
 * ```
 * $message = 'Ready your ammunition; we attack at dawn.';
 * $key = hash('sha256', 'Tropotek');
 *
 * $enc = new Encrypt($key)
 * $encrypted = $enc->safeEncrypt($message);
 * $decrypted = $enc->safeDecrypt($encrypted);
 *
 * var_dump($encrypted, $decrypted);
 * ```
 *
 * @todo Find all usages of encrypt/decrypt and update to use safeEncrypt/safeDecrypt
 *       Rename current encrypt/decrypt methods to basicEncrypt/basicDecrypt
 *       Rename safeEncrypt/safeDecrypt to encrypt/decrypt
 *       Find all usages of encrypt/decrypt and update to use safeEncrypt/safeDecrypt
 *       Also locate any uses of Config::get('system.encrypt') and update anything that uses it
 *       we may need to add a new specific config key for encryption Config::get('system.string.encrypt')???
 */
class Encrypt
{
    const string METHOD = 'aes-256-ctr';

    const string HASH_ALGO = 'sha256';

    /**
     * This key needs to be the same to encrypt and decrypt a value.
     * It is recommended that you use a 32-byte (256-bit) hex string.
     * Example:
     * ```
     * $key = hash('sha256', 'Tropotek');    // recommended
     * $key = hash('md5', 'Tropotek');       // works, but not recommended
     * ```
     */
    private string $key;


    public function __construct(string $key)
    {
        if (empty($key)) {
            throw new Exception('Invalid key');
        }
        $this->key = $key;
    }

    public static function create(string $key): Encrypt
    {
        return new self($key);
    }

    public function encrypt(string $string, bool $encode = true): string
    {
        //return $this->basicEncrypt($string);
        return $this->safeEncrypt($string);
    }

    public function decrypt(string $string, bool $encode = true): string
    {
        //return $this->basicDecrypt($string);
        return $this->safeDecrypt($string);
    }

    /**
     * Use this for basic encryption only.
     * It is not secure enough for any serious use.
     */
    public function basicEncrypt(string $string): string
    {
        $result = '';
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
            $char = chr(ord($char) + ord($keychar));
            $result .= $char;
        }
        return base64_encode($result);
    }

    /**
     * Use this for basic encryption only.
     * It is not secure enough for any serious use.
     */
    public function basicDecrypt(string $string): string
    {
        $result = '';
        $string = base64_decode($string);
        for($i = 0; $i < strlen($string); $i++) {
            $char = substr($string, $i, 1);
            $keychar = substr($this->key, ($i % strlen($this->key)) - 1, 1);
            $char = chr(ord($char) - ord($keychar));
            $result .= $char;
        }
        return $result;
    }

    /**
     * Encrypts (but does not authenticate) a message
     */
    public function unsafeEncrypt(string $message, bool $encode = true): string
    {
        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = openssl_random_pseudo_bytes($nonceSize ?: 255);

        $ciphertext = openssl_encrypt(
            $message,
            self::METHOD,
            $this->getBinKey(),     // encryption key (raw binary expected)
            OPENSSL_RAW_DATA,
            $nonce
        );

        // Now let's pack the IV and the ciphertext together
        // Naively, we can just concatenate
        if ($encode) {
            return base64_encode($nonce.$ciphertext);
        }
        // raw binary string
        return $nonce.$ciphertext;
    }

    /**
     * Decrypts (but does not verify) a message
     */
    public function unsafeDecrypt(string $message, bool $encoded = true): string
    {
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new \Tk\Exception('Encryption failure');
            }
        }

        $nonceSize = openssl_cipher_iv_length(self::METHOD);
        $nonce = mb_substr($message, 0, $nonceSize ?: 255, '8bit');
        $ciphertext = mb_substr($message, $nonceSize ?: 255, null, '8bit');

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::METHOD,
            $this->getBinKey(),
            OPENSSL_RAW_DATA,
            $nonce
        );

        return $plaintext ?: '';
    }


    /**
     * Encrypts then MACs a message
     */
    public function safeEncrypt(string $message, bool $encode = true): string
    {
        list($encKey, $authKey) = $this->splitKeys($this->getBinKey());

        // Pass to UnsafeCrypto::encrypt
        $ciphertext = $this->unsafeEncrypt($message, $encKey);

        // Calculate a MAC of the IV and ciphertext
        $mac = hash_hmac(self::HASH_ALGO, $ciphertext, $authKey, true);

        if ($encode) {
            return base64_encode($mac.$ciphertext);
        }
        // Prepend MAC to the ciphertext and return to caller (raw binary)
        return $mac.$ciphertext;
    }

    /**
     * Decrypts a message (after verifying integrity)
     */
    public function safeDecrypt(string $message, bool $encoded = true): string
    {
        list($encKey, $authKey) = $this->splitKeys($this->getBinKey());
        if ($encoded) {
            $message = base64_decode($message, true);
            if ($message === false) {
                throw new Exception('Encryption failure');
            }
        }

        // Hash Size -- in case HASH_ALGO is changed
        $hs = mb_strlen(hash(self::HASH_ALGO, '', true), '8bit');
        $mac = mb_substr($message, 0, $hs, '8bit');

        $ciphertext = mb_substr($message, $hs, null, '8bit');

        $calculated = hash_hmac(
            self::HASH_ALGO,
            $ciphertext,
            $authKey,
            true
        );

        if (!$this->hashEquals($mac, $calculated)) {
            throw new Exception('Encryption failure');
        }

        // Pass to UnsafeCrypto::decrypt
        $plaintext = $this->unsafeDecrypt($ciphertext, $encKey);

        return $plaintext;
    }

    /**
     * Splits a key into two separate keys; one for encryption
     * and the other for authentication
     *
     * @param string $masterKey (raw binary)
     * @return array (two raw binary strings)
     */
    protected function splitKeys(string $masterKey): array
    {
        // You really want to implement HKDF here instead!
        return [
            hash_hmac(self::HASH_ALGO, 'ENCRYPTION', $masterKey, true),
            hash_hmac(self::HASH_ALGO, 'AUTHENTICATION', $masterKey, true)
        ];
    }

    /**
     * Compare two strings without leaking timing information
     *
     * @ref https://paragonie.com/b/WS1DLx6BnpsdaVQW
     */
    protected function hashEquals(string $a, string $b): bool
    {
        if (function_exists('hash_equals')) {
            return hash_equals($a, $b);
        }
        $nonce = openssl_random_pseudo_bytes(32);
        return hash_hmac(self::HASH_ALGO, $a, $nonce) === hash_hmac(self::HASH_ALGO, $b, $nonce);
    }

    protected function getBinKey(): string
    {
        $bin = hex2bin($this->key);
        if (false === $bin) {
            throw new Exception('Invalid key');
        }
        return $bin;
    }

}

