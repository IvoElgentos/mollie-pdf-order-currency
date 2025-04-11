<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Payment\Test\Integration\Model\Methods;

use Mollie\Payment\Model\Methods\Klarnapaylater;

class KlarnapaylaterMethodTest extends AbstractTestMethod
{
    protected $instance = Klarnapaylater::class;

    protected $code = 'klarnapaylater';
}
