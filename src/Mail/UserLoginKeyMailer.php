<?php

namespace LoginByKey\Mail;


class UserLoginKeyMailer extends \Gems_Mail_StaffMailer
{
    protected $key = '';

    protected $keyValidity = '';

    protected $keyValidityUnit = '';

    protected $url = '';

    public function afterRegistry()
    {
        parent::afterRegistry();
        $this->addMailFields($this->getLoginKeyMailFields());
    }

    /**
     * Return the mailfields for a password reset template
     * @return array
     */
    protected function getLoginKeyMailFields()
    {
        $result['login_key'] = $this->key;
        $result['login_key_url'] = $this->url;
        $result['login_key_valid'] = $this->keyValidity;
        $result['login_key_valid_unit'] = $this->keyValidityUnit;

        return $result;
    }

    public function setKey($key)
    {
        $this->key = $key;
        $this->mailFields['login_key'] = $key;
    }

    public function setKeyValidity($keyValidity)
    {
        $this->keyValidity = $keyValidity;
        $this->mailFields['login_key_valid'] = $keyValidity;
    }

    public function setKeyValidityUnit($unit)
    {
        $this->keyValidityUnit = $unit;
        $this->mailFields['login_key_valid_unit'] = $unit;
    }

    public function setUrl($url)
    {
        $this->url = $url;
        $this->mailFields['login_key_url'] = $url;
    }
}
