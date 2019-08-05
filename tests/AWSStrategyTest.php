<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;
use Aws\Kms\KmsClient;

final class AWSStrategyTest extends TestCase
{
    public function setup()
    {
        $this->settings = [
            'keyId' => 'mocked',
            'region' => 'eu-west-2',
            'keySpec' => 'AES_256'
        ];

    }

    /**
     * @expectedException Exception
     */
    public function test_cannot_call_generateDataKey_without_kmsClient()
    {
        $awsStrategyWithoutKms = new \AndyBeak\EnvelopeEncryption\Strategies\AWSStrategy($this->settings);
        $awsStrategyWithoutKms->generateDataKey();
    }

    /**
     * @expectedException Exception
     */
    public function test_cannot_call_decryptDataKey_without_kmsClient()
    {
        $awsStrategyWithoutKms = new \AndyBeak\EnvelopeEncryption\Strategies\AWSStrategy($this->settings);
        $awsStrategyWithoutKms->decryptDataKey('dummy');
    }


}