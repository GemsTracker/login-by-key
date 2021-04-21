<?php

namespace Gems\LoginByKey\User\Form;

use MUtil\Translate\TranslateableTrait;

class LoginKeyForm extends \Gems_User_Form_OrganizationFormAbstract
{
    use TranslateableTrait;

    public function getSubmitButtonLabel()
    {
        return $this->_('Request login url');
    }

    /**
     * Retrieve the header title to display
     *
     * @return string
     */
    /*protected function getTitle()
    {
        return $this->_('Login');
    }*/

    public function loadDefaultElements()
    {
        $this->getOrganizationElement();
        $element = $this->getUserNameElement();
        $element->setLabel($this->_('Email or username'));
    }
}
