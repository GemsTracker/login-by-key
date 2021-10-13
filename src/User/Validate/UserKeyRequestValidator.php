<?php

namespace Gems\LoginByKey\User\Validate;

class UserKeyRequestValidator implements \Zend_Validate_Interface
{
    /**
     * The error message
     *
     * @var string
     */
    protected $_message;

    /**
     *
     * @var \Gems_User_Validate_GetUserInterface
     */
    protected $_userSource;

    /**
     *
     * @var \Zend_Translate
     */
    protected $translate;

    /**
     *
     * @param \Gems_User_Validate_GetUserInterface $userSource The source for the user
     * @param \Zend_Translate $translate
     */
    public function __construct(\Gems_User_Validate_GetUserInterface $userSource, \Zend_Translate $translate)
    {
        $this->_userSource = $userSource;
        $this->translate   = $translate;
    }

    /**
     *
     * @param string $message Default message for standard login fail.
     */
    public function getMessages()
    {
        return array($this->_message);
    }

    /**
     * Returns true if and only if $value meets the validation requirements
     *
     * If $value fails validation, then this method returns false, and
     * getMessages() will return an array of messages that explain why the
     * validation failed.
     *
     * @param  mixed $value
     * @param  mixed $content
     * @return boolean
     * @throws \Zend_Validate_Exception If validation of $value is impossible
     */
    public function isValid($value, $context = array())
    {
        $this->_message = null;

        $user = $this->_userSource->getUser();

        if (! ($user->isActive() && $user->canLoginByKey() && $user->isAllowedOrganization($context['organization']))) {
            $this->_message = $this->translate->_('User not found or no e-mail address known or user cannot login.');
        }

        return (boolean) ! $this->_message;
    }
}
