<?php
use Joomla\CMS\MVC\Controller\BaseController;

defined('_JEXEC') or die;

class ProjectsController extends BaseController
{
    public function display($cachable = false, $urlparams = array())
    {
        $view = $this->input->getString('view');

        if ($view == 'todos')
        {
            $contractID = $this->input->getInt('contractID', 0);
            $session = JFactory::getSession();
            if ($contractID != 0)
            {
                $session->set('createTodoFor', $contractID);
            }
            else
            {
                $session->clear('createTodoFor');
            }
        }
        if ($view == 'todos') {
            $v = $this->getView('todos', 'html');
            $format = $this->input->getString('format', 'html');
            $layout = ($format != 'html') ? 'print' : 'default';
            $this->input->set('layout', $layout);
            $v->setLayout($layout);
        }
        if ($view == 'statv2') {
            $itemID = $this->input->getInt('itemID', 0);
            $v = $this->getView('statv2', 'html');
            $layout = ($itemID > 0) ? 'item' : 'default';
            $this->input->set('layout', $layout);
            $v->setLayout($layout);
        }
        if ($view == 'catalogs') {
            $activeProject = ProjectsHelper::getActiveProject('');
            if ($activeProject != '') {
                $tip = ProjectsHelper::getProjectType($activeProject);
                $layout = ProjectsHelper::getProjectTypeName($tip);
                $this->input->set('layout', $layout);
                $v = $this->getView('catalogs', 'html');
                $v->setLayout($layout);
            }
        }
        $this->saveClick($view);
        return parent::display($cachable, $urlparams);
    }

    /**
     * Записывает клик юзера по меню
     * @param string $view Название текущей вьюшки
     * @throws Exception
     * @since 2.0.1-alpha.3
     */
    private function saveClick(string $view): void
    {
        $menu = $this->input->getString('menu');
        $from = $this->input->getString('from');
        if ($menu !== null && $from !== null) {
            $model = $this->getModel('Userclick', 'ProjectsModel');
            $data['view_from'] = $from;
            $data['view_to'] = $view;
            $data['menu'] = $menu;
            $uri = JUri::getInstance();
            $uri->delVar('menu');
            $uri->delVar('from');
            $query = $uri->getQuery(true);
            $uri->setQuery($query);
            $model->save($data);
            $this->setRedirect($uri->render())->redirect();
            jexit();
        }
    }
}
