<?php

namespace Gems\LoginByKey\Controller;


use Gems\LoginByKey\User\Form\LoginKeyForm;
use Gems\LoginByKey\User\LoginStatusTracker;

class LoginByKeyController extends \Gems_Controller_Action
{
    /**
     * @var \Gems_Loader
     */
    public $loader;

    protected $indexParams = [
        'loginKeyForm'       => 'createLoginKeyForm',
        'loginStatusTracker' => 'getLoginStatusTracker',
        'resetParam'         => 'reset',
    ];

    protected $indexSnippets = [
        'Login\\UserRequestLoginKeyFormSnippet',
        'Login\\TwoFactorCheckSnippet',
        //'Login\\CheckPasswordChangeRequiredSnippet',
        'Login\\SetAsCurrentUserSnippet',
        'Login\\RedirectToRequestSnippet',
        'Login\\GotoStartPageSnippet',
    ];


    protected $loginKeyForm;

    protected function createLoginKeyForm()
    {
        if (!$this->loginKeyForm) {
            $form = new LoginKeyForm();
            $this->loader->applySource($form);
            $this->loginKeyForm = $form;
        }
        return $this->loginKeyForm;
    }

    /**
     *
     * @return \Gems\User\LoginStatusTracker
     */
    public function getLoginStatusTracker()
    {
        return $this->loader->getUserLoader()->getLoginStatusTracker();
    }

    public function indexAction()
    {
        $this->initHtml();
        if ($this->indexSnippets && $this->useHtmlView) {
            $params = $this->_processParameters($this->indexParams);

            $request = $this->getRequest();
            $reset = (boolean) $request->getParam($this->indexParams['resetParam'], false);
            if ($reset && isset($params['loginStatusTracker']) && $params['loginStatusTracker'] instanceof LoginStatusTracker) {
                $params['loginStatusTracker']->setLoginByKey(false);
            }

            $sparams['request']           = $request;
            $sparams['resetParam']        = $params['resetParam'];
            $sparams['snippetList']       = $this->indexSnippets;
            $sparams['snippetLoader']     = $this->getSnippetLoader();
            $sparams['snippetParameters'] = $params;

            $this->addSnippets('SequenceSnippet', $sparams);

            return;
        }
    }

    /**
     *
     * @param array $input
     * @return array
     */
    protected function _processParameters(array $input)
    {
        $output = [];

        foreach ($input as $key => $value) {
            if (is_string($value) && method_exists($this, $value)) {
                $value = $this->$value($key);

                if (is_integer($key) || ($value === null)) {
                    continue;
                }
            }
            $output[$key] = $value;
        }

        return $output;
    }
}
