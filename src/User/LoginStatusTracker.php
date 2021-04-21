<?php

namespace Gems\LoginByKey\User;


class LoginStatusTracker extends \Gems\User\LoginStatusTracker
{
    protected function _getDefaults()
    {
        $defaults = parent::_getDefaults();
        $defaults['loginByKey'] = false;
        return $defaults;
    }

    /**
     * @return boolean
     */
    public function isLoginByKey()
    {
        return $this->_session->data['loginByKey'];
    }

    /**
     * @param bool $value
     * @return $this
     */
    public function setLoginByKey($value = true)
    {
        $this->_session->data['loginByKey'] = $value;

        return $this;
    }
}
