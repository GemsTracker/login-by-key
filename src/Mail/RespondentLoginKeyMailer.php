<?php

namespace Gems\LoginByKey\Mail;

class RespondentLoginKeyMailer extends \Gems_Mail_RespondentMailer
{
    use UserLoginKeyMailerTrait;

    public function __construct(\Gems_User_User $user)
    {
        $this->organizationId = $user->getLoginName();
        $this->patientId = $user->getCurrentOrganization();
        $this->user = $user;
    }

    public function afterRegistry()
    {
        if (!$this->user instanceof \Gems_User_User) {
            $this->user = $this->loader->getUserLoader()->getUser(null, null);
        }

        parent::afterRegistry();

        $mailFields = $this->user->getMailFields();
        $this->addMailFields($mailFields);

        $this->setFrom($this->user->getFrom());
        $this->addTo($this->user->getEmailAddress(), $this->user->getFullName());
        $this->setLanguage($this->user->getLocale());
    }
}
