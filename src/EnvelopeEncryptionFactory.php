<?php namespace AndyBeak\EnvelopeEncryption;

use AndyBeak\EnvelopeEncryption\Enum\KeyStoresEnum;
use AndyBeak\EnvelopeEncryption\Interfaces\KeystoreProviderInterface;
use AndyBeak\EnvelopeEncryption\Strategies\AWSStrategy;
use Aws\Kms\KmsClient;

class EnvelopeEncryptionFactory
{

    /**
     * @param KeyStoresEnum $keystoreProvider
     * @param array $settings
     * @return EnvelopeEncryption
     * @throws \Exception
     */
    public static function create(KeyStoresEnum $keystoreProvider, array $settings): EnvelopeEncryption
    {
        $envelopeEncryptionObject = new EnvelopeEncryption();

        $keystoreProviderObject = self::createKeyStoreObject($keystoreProvider, $settings);

        $envelopeEncryptionObject->setKeystoreProvider($keystoreProviderObject);

        return $envelopeEncryptionObject;
    }

    /**
     * @param KeyStoresEnum $keystoreProvider
     * @param array $settings
     * @return KeystoreProviderInterface
     * @throws \Exception
     */
    private static function createKeyStoreObject(KeyStoresEnum $keystoreProvider, array $settings): KeystoreProviderInterface
    {
        switch ($keystoreProvider) {
            case KeyStoresEnum::AWS:
                $keystoreProviderObject = self::makeAWSstrategy($settings);
                break;
            default:
                throw new \InvalidArgumentException('Provider not implemented yet.');
        }

        return $keystoreProviderObject;
    }

    /**
     * @param array $settings
     * @return KeystoreProviderInterface
     * @throws \Exception
     */
    private static function makeAWSstrategy(array $settings): KeystoreProviderInterface
    {
        $keystoreProviderObject = new AWSStrategy($settings);

        $kmsClient = self::makeKMSclient($settings);

        $keystoreProviderObject->setKmsClient($kmsClient);

        return $keystoreProviderObject;
    }

    /**
     * @param array $settings
     * @return KmsClient
     * @throws \Exception
     */
    private static function makeKMSclient(array $settings): KmsClient
    {
        try {

            $kmsClient = new KmsClient([
                'profile' => 'default',
                'version' => '2014-11-01',
                'region' => $settings['region'] ?? 'eu-west-2'
            ]);

        } catch (Aws\Exception\CredentialsException $e) {
            // lose the stack trace, but avoid disclosing sensitive information
            throw new \Exception('AWS credentials missing');
        }

        return $kmsClient;
    }
}