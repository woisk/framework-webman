<?php
declare(strict_types=1);

namespace Webman\Container\Exception;

use Exception;
use Psr\Container\ContainerExceptionInterface;

class CircularDependencyException extends Exception implements ContainerExceptionInterface
{
    //
}
