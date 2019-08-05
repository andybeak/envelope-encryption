## Envelope encryption helper
[![Build Status](https://travis-ci.com/andybeak/envelope-encryption.svg?branch=master)](https://travis-ci.com/andybeak/envelope-encryption)
[![Maintainability](https://api.codeclimate.com/v1/badges/0019d18afe2250460c6c/maintainability)](https://codeclimate.com/github/andybeak/envelope-encryption/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/0019d18afe2250460c6c/test_coverage)](https://codeclimate.com/github/andybeak/envelope-encryption/test_coverage)

This package makes it more convenient to perform envelope encryption. 

This pattern of encryption involves using a secure key store to hold a master key.  Each time that you encrypt a piece of data you generate a new random key to use.  This key is encrypted with the master key and stored alongside your data.

Using this pattern means that you do not ever need to deploy your master key to a server.  The master key remains in the secure key storage.  

Note that this is an anti-pattern for high-velocity data encryption.  Each time that you encrypt something you will be making an HTTPS call to your key provider, which obviously adds network I/O to your response time.

An alternative pattern is to use the same single data key for all of your records.  You can decrypt this key and store it in your configuration object to avoid having to make repeated calls to KMS.    

## Usage

### Create an object using KMS as a backing service

Create an instance of the object using the factory.  It accepts an enum of the type of provider and the settings to use in constructing the provider.

    use \AndyBeak\EnvelopeEncryption\EnvelopeEncryptionFactory; 
    use \AndyBeak\EnvelopeEncryption\Enum\KeyStoresEnum;
    
    $settings = [
        'keyId' => 'arn:aws:kms:us-west-2:111122223333:key/1234abcd-12ab-34cd-56ef-1234567890ab',
        'region' => 'eu-west-2',
        'keySpec' => 'AES_256'
    ];   
    $envelopeEncryption = EnvelopeEncryptionFactory::create(new KeystoresEnum(KeystoresEnum::AWS), $settings);
    
    
Only the `keyId` is mandatory and the other values will default to the ones shown if omitted.

### Encrypting and decrypting

Once you have an `EnvelopeEncryption` object you can call the `encrypt` method as follows:

    $envelopeEncrypted = $envelopeEncryption->encrypt('Hello World');    
    
This returns an associative array in this format:

    return [
        'ciphertext' => 'The encrypted string'
        'nonce' => 'A nonce that you must supply when decrypting',
        'encryptedDataKey' => 'The encrypted copy of the key that was used to encrypt the data'
    ]; 

You need to store all three pieces of data!

To decrypt you call `decrypt` and supply the information that was returned from `encrypt`:
        
    $plaintext = $envelopeEncryption->decrypt(
        $envelopeEncrypted['ciphertext'],
        $envelopeEncrypted['nonce'],
        $envelopeEncrypted['encryptedDataKey']
    );
    var_dump($plaintext);
    // string(11) "Hello World"

### Full example

    <?php
    
    require 'vendor/autoload.php';
    
    use \AndyBeak\EnvelopeEncryption\EnvelopeEncryptionFactory;
    use \AndyBeak\EnvelopeEncryption\Enum\KeyStoresEnum;
    
    $settings = [
        'keyId' => 'arn:aws:kms:us-west-2:111122223333:key/1234abcd-12ab-34cd-56ef-1234567890ab',
        'region' => 'eu-west-2',
        'keySpec' => 'AES_256'
    ];
    
    $envelopeEncryption = EnvelopeEncryptionFactory::create(new KeystoresEnum(KeystoresEnum::AWS), $settings);
    
    $envelopeEncrypted = $envelopeEncryption->encrypt('Hello World');
    
    $plaintext = $envelopeEncryption->decrypt(
        $envelopeEncrypted['ciphertext'],
        $envelopeEncrypted['nonce'],
        $envelopeEncrypted['encryptedDataKey']
    );
    
    var_dump($plaintext);
    // string(11) "Hello World"
    
 ## Backing service
 
 Currently this package only supports AWS KMS, but other services like Hashicorp Vault should be easy to set up by adding a new implementation of KeystoreProviderInterface
 
 ### KMS
 You'll need a key set up in AWS KMS.
 
 The SDK should detect the credentials from one of the following:
  
 * environment variables (via AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY), 
 * an [AWS credentials INI](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_profiles.html) file in your HOME directory, 
 * AWS Identity and Access Management (IAM) [instance profile credentials](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_assume_role.html), 
 * or [credential providers](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials_provider.html).
 
 More information is available on the [AWS PHP SDK documentation page](https://docs.aws.amazon.com/sdk-for-php/v3/developer-guide/guide_credentials.html).
