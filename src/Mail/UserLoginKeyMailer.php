<?php

namespace Gems\LoginByKey\Mail;


class UserLoginKeyMailer extends \Gems_Mail_StaffPasswordMailer implements LoginKeyMailerInterface
{
    use UserLoginKeyMailerTrait;

    public function __construct($user)
    {
        if ($user instanceof \Gems_User_User) {
            $this->user = $user;
        }
    }


    public function afterRegistry()
    {
        if (!$this->user instanceof \Gems_User_User) {
            $this->user = $this->loader->getUserLoader()->getUser(null, null);
        }

        \Gems_Mail_MailerAbstract::afterRegistry();

        $mailFields = $this->user->getMailFields();
        $this->addMailFields($mailFields);

        $this->setFrom($this->user->getFrom());
        $this->addTo($this->user->getEmailAddress(), $this->user->getFullName());
        $this->setLanguage($this->user->getLocale());
    }
}
