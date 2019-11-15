<?php
use Joomla\CMS\MVC\Controller\AdminController;
defined('_JEXEC') or die;

class ProjectsControllerSettings extends AdminController
{
    public function __construct($config = array())
    {
        $return = base64_encode($_SERVER['HTTP_REFERER']);
        $this->url = "index.php?option=com_projects&view=setting&layout=edit&return={$return}";
        parent::__construct($config);
    }

    public function getModel($name = 'Setting', $prefix = 'ProjectsModel', $config = array())
    {
        return parent::getModel($name, $prefix, $config);
    }

    public function contracts_v2()
    {
        $this->url .= "&tab=contracts_v2";
        $this->setRedirect($this->url);
        $this->redirect();
    }

    public function building()
    {
        $this->url .= "&tab=building";
        $this->setRedirect($this->url);
        $this->redirect();
    }

    private $url;
}
