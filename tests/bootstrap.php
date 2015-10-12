<?php

/**
 * This file is part of the ProxmoxVE PHP API wrapper library (unofficial).
 *
 * @copyright 2015 César Antáres <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

$autoload = dirname(__DIR__) . '/vendor/autoload.php';

if (!file_exists($autoload)) {
    echo "Please install project runing:\n\tcomposer install\n\n";
    exit("Don't have composer?\n\thttps://getcomposer.org/download/\n\n");
}

$loader = include $autoload;
$loader->addPsr4('ZzAntares\\CfdiWrapper\\', __DIR__);
