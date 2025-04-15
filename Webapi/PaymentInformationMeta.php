<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Mollie\Payment\Webapi;

use Mollie\Payment\Api\Data\IssuerInterface;
use Mollie\Payment\Api\Data\IssuerInterfaceFactory;
use Mollie\Payment\Api\Data\TerminalInterface;
use Mollie\Payment\Api\Data\TerminalInterfaceFactory;
use Mollie\Payment\Api\Data\MethodMetaInterface;
use Mollie\Payment\Api\Data\MethodMetaInterfaceFactory;
use Mollie\Payment\Api\Webapi\PaymentInformationMetaInterface;
use Mollie\Payment\Block\Form\Pointofsale;
use Mollie\Payment\Config;
use Mollie\Payment\Service\Mollie\AvailableTerminals;
use Mollie\Payment\Service\Mollie\GetIssuers;
use Mollie\Payment\Service\Mollie\MollieApiClient;
use Mollie\Payment\Service\Mollie\PaymentMethods;

class PaymentInformationMeta implements PaymentInformationMetaInterface
{
    /**
     * @var MethodMetaInterfaceFactory
     */
    private $methodMetaFactory;
    /**
     * @var GetIssuers
     */
    private $getIssuers;
    /**
     * @var PaymentMethods
     */
    private $paymentMethods;
    /**
     * @var MollieApiClient
     */
    private $mollieApiClient;
    /**
     * @var Pointofsale
     */
    private $pointofsale;
    /**
     * @var AvailableTerminals
     */
    private $availableTerminals;
    /**
     * @var Config
     */
    private $config;
    /**
     * @var IssuerInterfaceFactory
     */
    private $issuerFactory;
    /**
     * @var TerminalInterfaceFactory
     */
    private $terminalFactory;

    public function __construct(
        Config $config,
        MethodMetaInterfaceFactory $methodMetaFactory,
        MollieApiClient $mollieApiClient,
        PaymentMethods $paymentMethods,
        GetIssuers $getIssuers,
        Pointofsale $pointofsale,
        AvailableTerminals $availableTerminals,
        IssuerInterfaceFactory $issuerFactory,
        TerminalInterfaceFactory $terminalFactory
    ) {
        $this->methodMetaFactory = $methodMetaFactory;
        $this->mollieApiClient = $mollieApiClient;
        $this->paymentMethods = $paymentMethods;
        $this->getIssuers = $getIssuers;
        $this->pointofsale = $pointofsale;
        $this->availableTerminals = $availableTerminals;
        $this->config = $config;
        $this->issuerFactory = $issuerFactory;
        $this->terminalFactory = $terminalFactory;
    }

    public function getPaymentMethodsMeta(): array
    {
        $meta = [];
        foreach ($this->paymentMethods->getCodes() as $code) {
            $meta[$code] = $this->methodMetaFactory->create([
                'code' => $code,
                'issuers' => $this->getIssuers($code),
                'terminals' => $this->getTerminals($code),
            ]);
        }

        return $meta;
    }

    public function getIssuers(string $code): array
    {
        static $mollieApiClient;

        if (!$mollieApiClient) {
            $mollieApiClient = $this->mollieApiClient->loadByStore();
        }

        $issuers = $this->getIssuers->execute($mollieApiClient, $code, 'list');
        if ($issuers === null) {
            return [];
        }

        return array_map(function (array $issuer) {
            $issuer['images'] = $issuer['image'];
            return $this->issuerFactory->create($issuer);
        }, $issuers);
    }

    private function getTerminals(string $code): array
    {
        if ($code != 'mollie_methods_pointofsale' ||
            !$this->config->isMethodActive('mollie_methods_pointofsale')
        ) {
            return [];
        }

        return array_map(function (array $terminal) {
            return $this->terminalFactory->create($terminal);
        }, $this->availableTerminals->execute());
    }
}
