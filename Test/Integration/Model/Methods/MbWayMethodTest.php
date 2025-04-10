<?php
/*
 * Copyright Magmodules.eu. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mollie\Payment\Test\Integration\Model\Methods;

use Mollie\Payment\Model\Methods\MbWay;

class MbWayMethodTest extends AbstractTestMethod
{
    protected $instance = MbWay::class;

    protected $code = 'mbway';
}
