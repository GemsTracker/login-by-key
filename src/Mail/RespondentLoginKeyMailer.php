<?php

namespace Gems\LoginByKey\Mail;

class RespondentLoginKeyMailer extends \Gems_Mail_RespondentMailer implements LoginKeyMailerInterface
{
    use UserLoginKeyMailerTrait;

    public function __construct(\Gems_User_User $user)
    {
        $this->organizationId = $user->getCurrentOrganization()->getId();
        $this->patientId = $user->getLoginName();
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
