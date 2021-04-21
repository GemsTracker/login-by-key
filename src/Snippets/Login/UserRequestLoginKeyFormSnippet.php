<?php

namespace Gems\LoginByKey\Snippets\Login;


use Gems\LoginByKey\User\UserLoginByKeyRepository;
use Gems\Snippets\FormSnippetAbstract;
use Gems\LoginByKey\User\Form\LoginKeyForm;
use Gems\User\LoginStatusTracker;
use Gems\LoginByKey\User\Validate\UserKeyRequestValidator;

class UserRequestLoginKeyFormSnippet extends FormSnippetAbstract
{
    /**
     * @var \Gems_User_User
     */
    protected $currentUser;

    /**
     * @var \Gems_Loader
     */
    protected $loader;

    /**
     * @var LoginKeyForm
     */
    protected $loginKeyForm;

    /**
     * @var LoginStatusTracker
     */
    protected $loginStatusTracker;

    /**
     * @var \Zend_Controller_Request_Abstract
     */
    protected $request;

    protected $resetParam;

    /**
     * The name of the action to forward to after form completion
     *
     * @var string
     */
    protected $routeAction = 'index';

    /**
     * @var UserLoginByKeyRepository
     */
    protected $userLoginByKeyRepository;

    /**
     * @var \Gems_Util
     */
    protected $util;

    /**
     * Creates an empty form. Allows overruling in sub-classes.
     *
     * @param mixed $options
     * @return \Zend_Form
     */
    protected function createForm($options = null)
    {
        return $this->loginKeyForm;
    }

    public function afterRegistry()
    {
        parent::afterRegistry();
        $this->saveLabel = $this->_('Request login url');

        $userLoginByKeyRepository = new UserLoginByKeyRepository();
        $this->loader->applySource($userLoginByKeyRepository);
        $this->userLoginByKeyRepository = $userLoginByKeyRepository;
    }

    protected function addFormElements(\Zend_Form $form)
    {
        $this->addSaveButton();
    }

    /**
     * overrule to add your own buttons.
     *
     * @return \Gems_Menu_MenuList
     */
    protected function getMenuList()
    {

    }

    /**
     * Retrieve the header title to display
     *
     * @return string
     */
    protected function getTitle()
    {
        return $this->_('Login');
    }

    /**
     * Logins a user from a login key
     *
     * @param $loginKey
     * @return bool
     */
    protected function isSuccesfullLoginByKey($loginKey)
    {
        if ($loginKey) {
            $hashedkey = $this->userLoginByKeyRepository->hashKey($loginKey);

            $user = $this->userLoginByKeyRepository->getUserByLoginKey($hashedkey);

            if ($user->isActive()) {
                $this->userLoginByKeyRepository->removeLoginKeyFromUser($user);
                $this->loginStatusTracker
                    ->setLoginByKey(true)
                    ->setUser($user);

                return true;
            }
        }
        return false;
    }

    protected function processForm()
    {
        // Check job monitors as long as the login form is being processed
        $this->util->getMonitor()->checkMonitors();

        // Start the real work
        $this->loadForm();

        $orgId = null;
        if ($this->request->isPost()) {
            $orgId = $this->_form->getActiveOrganizationId();

            if ($orgId && ($this->currentUser->getCurrentOrganizationId() != $orgId)) {
                $this->currentUser->setCurrentOrganization($orgId);
            }
        }

        if ($this->loginStatusTracker->isLoginByKey()) {
            return false;
        }

        if ($loginKey = $this->request->getParam('key')) {


            if ($this->isSuccesfullLoginByKey($loginKey)) {
                $this->afterSaveRouteUrl = [
                    $this->request->getControllerKey() => $this->request->getControllerName(),
                    $this->request->getActionKey() => $this->request->getActionName(),
                ];
                $this->resetRoute = true;
            } else {
                $this->addMessage($this->_('User not found'));
            }
        }

        return parent::processForm();
    }

    protected function saveData()
    {
        $user = $this->_form->getUser();

        $validator = new UserKeyRequestValidator($this->_form, $this->translate);
        $validUser = $validator->isValid(null, $this->request->getPost());
        $errors = null;

        if ($validUser) {
            $result = $user->authenticate(null, false);

            if (!$result->isValid()) {
                $this->addMessage($result->getMessages());
                $this->addMessage($this->_('For that reason you cannot request a login key.'));
                return;
            }

            try {
                $this->userLoginByKeyRepository->sendUserLoginEmail($user);
            } catch (\Exception $e) {
                $this->accesslog->logChange(
                    $this->request,
                    sprintf(
                        "CanLoginByKeyTrait %s requested a login key but got an error. %s",
                        $this->_form->getUserNameElement()->getValue(),
                        $e->getMessage()
                    )
                );
            }
        }

        if (!$errors || $validUser === false) {
            // Everything went OK! Or the user isn't valid but we do not want you to know;
            $this->addMessage($this->_(
                'If the entered username or e-mail is valid, we have sent you an e-mail with a login link. Click on the link in the e-mail.'
            ));

            if ($validUser) {
                $this->accesslog->logChange($this->request);
            }
        }

        return 0;
    }

    protected function sendUserLoginEmail(\Gems_User_User $user)
    {
        if ($user->isStaff()) {
            $mail = $this->loader->getMailLoader()->getMailer('staffPassword', $user->getUserId());
        } else {
            $mail = $this->loader->getMailLoader()->getMailer('respondentPassword', $user->getUserLoginId(), $user->getBaseOrganizationId());
        }

        if ($mail->setLoginByKeyTemplate()) {
            $mail->send();
            $this->addMessage($this->_('Create account mail sent'));
            $this->setAfterSaveRoute();
        } else {
            $this->addMessage($this->_('No default Create Account mail template set in organization or project'));
        }
    }
}
