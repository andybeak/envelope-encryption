<?php namespace AndyBeak\EnvelopeEncryption\Interfaces;

interface KeystoreProviderInterface
{
    /**
     * This operation returns a plaintext copy of the data key and a copy that is encrypted under a customer master key
     * We use the plaintext key to encrypt the data and throw this away.
     * [
     *     'plaintextKey' => 'Use this to encrypt the data',
     *     'ciphertextKey' => 'Store this alongside the encrypted data'
     * ];
     * @return array
     */
    public function generateDataKey(): array;

    /**
     * Decrypt the stored datakey by using the master key.  We will use the plaintext key that this function returns
     * to decrypt the stored data.
     *
     * @param string $encryptedDataKey
     * @return string
     */
    public function decryptDataKey(string $encryptedDataKey): string;

}