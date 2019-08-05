<?php namespace AndyBeak\EnvelopeEncryption\Strategies;

use AndyBeak\EnvelopeEncryption\Interfaces\KeystoreProviderInterface;
use Aws\Kms\KmsClient;

class AWSStrategy implements KeystoreProviderInterface
{
    /**
     * @var KmsClient
     */
    private $kmsClient;

    /**
     * @var string
     */
    private $keyId;

    /**
     * @var string
     */
    private $keySpec;

    public function __construct(array $settings)
    {
        if (!isset($settings['keyId'])) {
            throw new \InvalidArgumentException('Mandatory KeyId not specified in settings');
        }
        $this->keyId = $settings['keyId'];

        $this->keySpec = $settings['keySpec'] ?? 'AES_256';
    }

    /**
     * This operation returns a plaintext copy of the data key and a copy that is encrypted under a customer master key
     * We use the plaintext key to encrypt the data and throw this away.
     *
     * @return array
     * @throws \Exception
     */
    public function generateDataKey(): array
    {
        if (!$this->kmsClient instanceof KmsClient) {
            throw new \Exception("Cannot call this method until client has been set.");
        }

        $result = $this->kmsClient->generateDataKey([
            'KeyId' => $this->keyId,
            'KeySpec' => $this->keySpec,
        ]);

        return [
            'plaintextKey' => $result['Plaintext'],
            'ciphertextKey' => $result['CiphertextBlob']
        ];
    }

    /**
     * Decrypt the stored datakey by using the master key.
     * We will use the plaintext key that this function returns to decrypt the stored data.
     *
     * @param string $encryptedDataKey
     * @return string
     * @throws \Exception
     */
    public function decryptDataKey(string $encryptedDataKey): string
    {
        if (!$this->kmsClient instanceof KmsClient) {
            throw new \Exception("Cannot call this method until client has been set.");
        }

        $dataKey = $this->kmsClient->decrypt([
            'CiphertextBlob' => $encryptedDataKey
        ]);

        return $dataKey['Plaintext'];
    }

    /**
     * @param KmsClient $kmsClient
     */
    public function setKmsClient(KmsClient $kmsClient): void
    {
        $this->kmsClient = $kmsClient;
    }
}