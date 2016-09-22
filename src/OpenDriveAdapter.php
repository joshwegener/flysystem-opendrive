<?php

namespace JoshWegener\FlysystemOpenDrive;

use League\Flysystem\Adapter\Polyfill\NotSupportingVisibilityTrait;
use League\Flysystem\AdapterInterface;
use League\Flysystem\Config;

class OneDriveAdapter implements AdapterInterface
{
    use NotSupportingVisibilityTrait;
}
