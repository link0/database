<?php

namespace Link0\Database;

use Exception;

/**
 * Problem defines any problem with a database call
 * I'd rather have this be an interface, but then all
 * problems should explicitly extend Exception and implement
 * everything that Throwable also defines.
 *
 * @package Link0\Database
 */
abstract class Problem extends Exception
{
}
