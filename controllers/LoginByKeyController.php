<?php

class LoginByKeyController extends \Gems_Controller_Action
{
    /**
     * @var \Gems_Loader
     */
    public $loader;

    protected $indexParams = [
        'loginKeyForm' => 'createLoginKeyForm',
        'loginStatusTracker' => 'getLoginStatusTracker',
    ];

    protected $indexSnippets = [
        'Login\\UserRequestLoginKeyFormSnippet',
    ];


    protected $loginKeyForm;

    protected function createLoginKeyForm()
    {
        if (!$this->loginKeyForm) {
            $form = new \LoginByKey\User\Form\LoginKeyForm();
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
        $params = $this->_processParameters($this->indexParams);
        $this->addSnippets($this->indexSnippets, $params);
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
