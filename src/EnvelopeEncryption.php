<?php namespace AndyBeak\EnvelopeEncryption;

use AndyBeak\EnvelopeEncryption\Interfaces\KeystoreProviderInterface;

class EnvelopeEncryption
{
    /**
     * @var KeystoreProviderInterface
     */
    private $keystoreProvider;

    /**
     * @param string $input
     * @return array
     * @throws \Exception
     */
    public function encrypt(string $input): array
    {
        if (strlen($input) === 0) {
            throw new \InvalidArgumentException('Cannot encrypt empty string');
        }

        $dataKey = $this->keystoreProvider->generateDataKey();

        $nonce = random_bytes(SODIUM_CRYPTO_SECRETBOX_NONCEBYTES);

        $ciphertext = sodium_crypto_secretbox(
            $input,
            $nonce,
            $dataKey['plaintextKey']
        );

        return [
            'ciphertext' => $ciphertext,
            'nonce' => $nonce,
            'encryptedDataKey' => $dataKey['ciphertextKey']
        ];
    }

    /**
     * @param string $ciphertext
     * @param string $nonce
     * @param string $encryptedDataKey
     * @return string
     */
    public function decrypt(string $ciphertext, string $nonce, string $encryptedDataKey): string
    {
        $decryptedDataKey = $this->keystoreProvider->decryptDataKey($encryptedDataKey);

        $plaintext = sodium_crypto_secretbox_open(
            $ciphertext,
            $nonce,
            $decryptedDataKey
        );

        return $plaintext;
    }

    public function setKeystoreProvider(KeystoreProviderInterface $keystoreProvider): void
    {
        $this->keystoreProvider = $keystoreProvider;
    }

}