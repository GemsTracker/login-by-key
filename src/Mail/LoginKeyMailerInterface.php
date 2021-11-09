<?php

namespace Gems\LoginByKey\Mail;

interface LoginKeyMailerInterface
{
    public function setKey($key);

    public function setKeyValidity($keyValidity);

    public function setKeyValidityUnit($unit);

    public function setUrl($url);
}
