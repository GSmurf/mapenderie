<?php
namespace Siplec\UserBundle;

use Symfony\Component\HttpKernel\Bundle\Bundle;

class SiplecUserBundle extends Bundle
{
    public function getParent()
    {
        return 'FOSUserBundle';
    }
}
