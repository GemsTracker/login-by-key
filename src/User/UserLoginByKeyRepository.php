<?php

namespace Gems\LoginByKey\User;


use Gems\LoginByKey\Mail\UserLoginKeyMailer;

class UserLoginByKeyRepository extends \MUtil_Translate_TranslateableAbstract
{
    /**
     * @var \Zend_Db_Adapter_Abstract
     */
    protected $db;

    /**
     * @var \Gems_Loader
     */
    protected $loader;

    protected $keyValidInterval = 'PT1H';

    /**
     *
     * @var string Algorithm for the PHP hash() function, E.G. sha256
     */
    protected $loginKeyHashAlgo = 'sha512';

    protected $mailTemplateCode = 'login-key';

    public function hashKey($key)
    {
        return hash($this->loginKeyHashAlgo, $key);
    }

    /**
     * Get the user having the reset key specified
     *
     * @param string $resetKey
     * @return \Gems_User_User But ! ->isActive when the user does not exist
     */
    public function getUserByLoginKey($loginKey)
    {
        $userLoader = $this->loader->getUserLoader();
        if ((null == $loginKey) || (0 == strlen(trim($loginKey)))) {
            return $userLoader->getUser(null, null);
        }

        $now = new \DateTimeImmutable();

        $select = $this->db->select();
        $select->from('gems__user_passwords', array())
            ->joinLeft('gems__user_logins', 'gup_id_user = gul_id_user', array("gul_user_class", 'gul_id_organization', 'gul_login'))
            ->where('gup_login_key = ?', $loginKey)
            ->where('gup_login_key_valid_until >= ?', $now->format('Y-m-d H:i:s'));

        // \MUtil_Echo::track($select->__toString());

        if ($row = $this->db->fetchRow($select, null, \Zend_Db::FETCH_NUM)) {
            // \MUtil_Echo::track($row);
            return $userLoader->getUser($row[2], $row[1]);
        }

        return $userLoader->getUser(null, null);
    }

    public function createLoginKey(\Gems_User_User $user)
    {
        $key = hash($this->loginKeyHashAlgo ,base64_encode(random_bytes(64)));
        return $key;
    }

    public function getLoginByKeyUrl(\Gems_User_User $user)
    {
        return $user->getBaseOrganization()->getLoginUrl() . '/login-by-key/index/key/';
    }

    /**
     * Logins a user from a login key
     *
     * @param $loginKey
     * @return bool
     */
    public function authenticateByLoginKey($loginKey)
    {
        if ($loginKey) {
            $hashedkey = $this->hashKey($loginKey);

            $user = $this->getUserByLoginKey($hashedkey);

            if ($user->isActive()) {
                $this->removeLoginKeyFromUser($user);
                return true;
            }
        }
        return false;
    }

    /**
     * Remove a login key and its validity from a user
     *
     * @param \Gems_User_User $user
     * @return int
     * @throws \Zend_Db_Adapter_Exception
     */
    public function removeLoginKeyFromUser(\Gems_User_User $user)
    {
        return $this->db->update('gems__user_passwords', [
            'gup_login_key' => null,
            'gup_login_key_valid_until' => null,
        ],
            [
                'gup_id_user = ?' => $user->getUserLoginId(),
            ]);
    }

    public function setLoginKey(\Gems_User_User $user, $validUntilInterval = null)
    {
        $key = $this->createLoginKey($user);

        if ($validUntilInterval === null) {
            $validUntilInterval = $this->keyValidInterval;
        }
        $now = new \DateTimeImmutable();
        try {
            $interval = new \DateInterval($validUntilInterval);
        } catch (\Exception $e) {
            $interval = \DateInterval::createFromDateString($validUntilInterval);
        }

        $validUntilDateTime = $now->add(new \DateInterval($validUntilInterval));

        $hashedKey = $this->hashKey($key);

        $result = $this->db->update('gems__user_passwords', [
            'gup_login_key' => $hashedKey,
            'gup_login_key_valid_until' => $validUntilDateTime->format('Y-m-d H:i:s'),
        ],
            [
                'gup_id_user = ?' => $user->getUserLoginId(),
            ]);

        if ($result) {
            return $key;
        }

        return null;
    }

    public function sendUserLoginEmail(\Gems_User_User $user, $validUntilInterval = null, $mailTemplateCode = null)
    {
        if ($user->getUserDefinitionClass() instanceof \Gems_User_RespondentUserDefinition) {
            $mail = $this->loader->getMailLoader()->getMailer('respondentLoginKey', $user);
        } else {
            $mail = $this->loader->getMailLoader()->getMailer('userLoginKey', $user);
        }

        $key = $this->setLoginKey($user, $validUntilInterval);
        $mail->setKey($key);
        $mail->setUrl($this->getLoginByKeyUrl($user) . $key);

        $this->setMailValidityUnit($mail, $validUntilInterval);

        if ($mailTemplateCode === null) {
            $mailTemplateCode = $this->mailTemplateCode;
        }

        if ($mail->setTemplateByCode($mailTemplateCode)) {
            $mail->send();

            return true;

        } else {
            throw new \Exception($this->_('No default Create Account mail template set in organization or project'));
        }
    }

    protected function setMailValidityUnit(UserLoginKeyMailer $mail, $validUntilInterval = null)
    {
        if ($validUntilInterval === null) {
            $validUntilInterval = $this->keyValidInterval;
        }

        if (strpos($validUntilInterval,  'PT') === 0 && strlen($validUntilInterval) > 3) {
            $time = str_replace(['PT', 'H', 'M'], '', $validUntilInterval);
            switch(substr($validUntilInterval, -1)) {
                case 'H':
                    $mail->setKeyValidityUnit($this->plural('hour', 'hour', $time));
                    break;
                case 'M':
                    $mail->setKeyValidityUnit($this->plural('minute', 'minutes', $time));
                    break;
            }
            $mail->setKeyValidity($time);
        } elseif (strpos($validUntilInterval,  'P') === 0 && strlen($validUntilInterval) > 2 && substr($validUntilInterval, -1) === 'D') {
            $time = str_replace(['P', 'D'], '', $validUntilInterval);
            $mail->setKeyValidityUnit($this->plural('day', 'days', $time));
            $mail->setKeyValidity($time);
        }
    }
}
