<?php

namespace Gems\LoginByKey\Mail;

/**
 *
 *
 * @package    Depar
 * @subpackage Mail
 * @copyright  Copyright (c) 2013 Erasmus MC
 * @license    New BSD License
 * @since      Class available since version 1.7.1
 */
class MailLoader extends \Gems_Mail_MailLoader
{
    public function afterRegistry()
    {
        parent::afterRegistry();
        $this->mailTargets['userLoginKey'] = 'User login key';
    }
}
