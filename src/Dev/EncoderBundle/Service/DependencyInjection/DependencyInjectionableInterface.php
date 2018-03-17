<?php

namespace Dev\EncoderBundle\Service\DependencyInjection;

interface DependencyInjectionableInterface
{
    public function getDependenceInjectionParameters();
    public function getDependenceInjectionServices();
}
