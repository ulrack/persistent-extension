<?php

/**
 * Copyright (C) GrizzIT, Inc. All rights reserved.
 * See LICENSE for license details.
 */

use GrizzIt\Configuration\Component\Configuration\PackageLocator;
use Ulrack\PersistentExtension\Common\UlrackPersistentExtensionPackage;

PackageLocator::registerLocation(
    __DIR__,
    UlrackPersistentExtensionPackage::PACKAGE_NAME
);
