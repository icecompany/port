<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelUserclicks extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                //Sorting                   //Filter        //Cross
                'cnt',                      'manager',
                'view_to',                  'query',
            );
        }

        $this->task = JFactory::getApplication()->input->getString('task', 'display');
        $this->return = ProjectsHelper::getReturnUrl();
        $this->userSettings = ProjectsHelper::getUserSettings();

        $this->titlesColumns = array( //Названия колонок для экспорта
            'num' => 'COM_PROJECTS_HEAD_CONTRACT_NUMBER_SHORT',
            'dat' => 'COM_PROJECTS_HEAD_CONTRACT_DATE_DOG',
            'stands' => 'COM_PROJECTS_HEAD_CONTRACT_STAND_SHORT',
            'project' => 'COM_PROJECTS_HEAD_CONTRACT_PROJECT',
            'exhibitor' => 'COM_PROJECTS_HEAD_CONTRACT_EXPONENT',
            'parent' => 'COM_PROJECTS_HEAD_CONTRACT_COEXP_BY',
            'todos' => 'COM_PROJECTS_HEAD_CONTRACT_ACTIVE_TODOS',
            'manager' => 'COM_PROJECTS_HEAD_CONTRACT_MANAGER',
            'status' => 'COM_PROJECTS_HEAD_CONTRACT_STATUS',
            'doc_status' => 'COM_PROJECTS_HEAD_CONTRACT_DOC_STATUS_SHORT',
            'amount' => 'COM_PROJECTS_HEAD_CONTRACT_AMOUNT',
            'payments' => 'COM_PROJECTS_HEAD_SCORE_PAYMENT',
            'debt' => 'COM_PROJECTS_HEAD_CONTRACT_DEBT',
        );

        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`view_to` as section, count(view_to) as cnt")
            ->from("`#__prj_user_clicks`")
            ->group("section");

        $input = JFactory::getApplication()->input;

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search))
        {
            $search = $db->q("%{$search}%");
            $query->where("(`view_to` LIKE {$search})");
        }

        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_array($manager)) {
            $manager = implode(", ", $manager);
            $query->where("`managerID` in ({$manager})");
        }

        /* Сортировка */
        $orderCol  = $this->state->get('list.ordering', 'cnt');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        //Лимит
        $this->setState('list.limit', 0);

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array('items' => array());

        foreach ($items as $item) {
            $arr = array();
            $menu = sprintf("COM_PROJECTS_MENU_%s", strtoupper($item->section));
            $arr['section'] = JText::sprintf($menu);
            $arr['cnt'] = $item->cnt;
            $result['items'][] = $arr;
        }

        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search', '', 'string');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', '', 'array');
        $this->setState('filter.manager', $manager);

        parent::populateState('cnt', 'desc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }

    private $task, $return, $userSettings, $titlesColumns;
}
