<?php

/**
 * This file is part of the CFDI Wrapper library.
 *
 * @copyright 2015 César Antáres <zzantares@gmail.com>
 * @license http://opensource.org/licenses/MIT The MIT License.
 */

function in_array_all($needles, $haystack)
{
    return !array_diff($needles, $haystack);
}
