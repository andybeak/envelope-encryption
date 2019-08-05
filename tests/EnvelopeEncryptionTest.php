<?php

declare(strict_types=1);
use PHPUnit\Framework\TestCase;
use \AndyBeak\EnvelopeEncryption\EnvelopeEncryption;
use AndyBeak\EnvelopeEncryption\Strategies\AWSStrategy;

class EnvelopeEncryptionTest extends TestCase
{
    /**
     * @var EnvelopeEncryption
     */
    private $envelopeEncryption;

    /**
     * @var array
     */
    private $exampleKey;

    /**
     * Multibyte string with emoji characters
     */
    const EMOJI_STRING = '😀 😁 😂🤣 😃';

    public function setUp()
    {
        parent::setUp();

        $this->exampleKey = [
            'plaintextKey' => base64_decode('v1PUYEzm5D1dh8t9z+0jm8ckKuh/8dBPHOxUAkUFcI8='),
            'ciphertextKey' => base64_decode('AQIDAHiMsxfXauMn1rfGCqPwfQ6EW/kYdiakljbFqSmHgal10wGW2wmZV3ycPF0e9FotujDcAAAAfjB8BgkqhkiG9w0BBwagbzBtAgEAMGgGCSqGSIb3DQEHATAeBglghkgBZQMEAS4wEQQMkhM+sJ0YPgYpPAQ5AgEQgDsYILSR+TT6LzZboR9Ctgw2jFkYRKO8W8KlLhkjow7JOVKvOpDPS56AQs7iveb7ntQdPOiZm0m/eqZ01A==')
        ];

        $stubKeystoreProvider = $this->createMock(AWSStrategy::class);

        $stubKeystoreProvider->method('generateDataKey')
            ->willReturn($this->exampleKey);

        $stubKeystoreProvider->method('decryptDataKey')
            ->willReturn($this->exampleKey['plaintextKey']);

        $this->envelopeEncryption = new EnvelopeEncryption();
        $this->envelopeEncryption->setKeystoreProvider($stubKeystoreProvider);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function test_cannot_encrypt_empty_string(): void
    {
        $this->envelopeEncryption->encrypt('');
    }

    /**
     * @dataProvider stringsProvider
     * @param $testString
     * @throws Exception
     */
    public function test_encrypt($testString): void
    {
        $previousNonce = '';
        $encrypted = $this->envelopeEncryption->encrypt($testString);
        // does it have all the keys
        $this->assertArrayHasKey('ciphertext', $encrypted);
        $this->assertArrayHasKey('nonce', $encrypted);
        $this->assertArrayHasKey('encryptedDataKey', $encrypted);
        // is it returning the encrypted cipher key correctly?
        $this->assertSame(
            $this->exampleKey['ciphertextKey'],
            $encrypted['encryptedDataKey']
        );
        // is a unique nonce being generated each time?
        $this->assertNotSame($encrypted['nonce'], $previousNonce);
        $previousNonce = $encrypted['nonce'];
    }

    /**
     * @dataProvider stringsProvider
     * @param $testString
     * @throws Exception
     */
    public function test_decrypt($testString): void
    {
        $encrypted = $this->envelopeEncryption->encrypt($testString);
        list($ciphertext, $nonce, $encryptedDataKey) = array_values($encrypted);
        $decrypted = $this->envelopeEncryption->decrypt($ciphertext, $nonce, $encryptedDataKey);
        $this->assertSame($testString, $decrypted);
    }

    /**
     * @return array
     */
    public function stringsProvider()
    {
        return [
            ['password1234'],
            ['1234'],
            ['password 1234'],
            ['password!1234'],
            ['password1234' . SELF::EMOJI_STRING]
        ];
    }
}