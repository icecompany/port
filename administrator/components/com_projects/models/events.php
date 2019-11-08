<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;

class ProjectsModelEvents extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'id',
                'manager',
                'dat', 'a.dat',
                'section',
                'action',
                'search',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select('`a`.`id`, `a`.`section`, `a`.`action`, `a`.`itemID`')
            ->select('`a`.`old_data`, `a`.`params`')
            ->select("DATE_FORMAT(`a`.`dat`,'%d.%m.%Y %H:%i') as `dat`")
            ->select("`u`.`name` as `manager`")
            ->from("`#__prj_user_action_log` as `a`")
            ->leftJoin("`#__users` as `u` ON `u`.`id` = `a`.`userID`");

        /* Фильтр */
        $search = $this->getState('filter.search');
        if (!empty($search)) {
            $search = $db->quote('%' . $db->escape($search, true) . '%', false);
            $query->where('(`a`.`params` LIKE ' . $search . ' OR `a`.`old_data` LIKE ' . $search . ')');
        }

        if (!ProjectsHelper::canDo('projects.access.events.full')) {
            $userID = JFactory::getUser()->id;
            $query->where("`a`.`userID` = {$userID}");
        }

        if (ProjectsHelper::canDo('projects.access.events.full')) {
            // Фильтруем по менеджеру.
            $manager = $this->getState('filter.manager');
            if (is_numeric($manager)) {
                $query->where('`a`.`userID` = ' . (int) $manager);
            }
        }
        // Фильтруем по разделу сайта.
        $section = $this->getState('filter.section');
        if (!empty($section) && $section != '') {
            $section = $db->quote($db->escape($section, true), false);
            $query->where('`a`.`section` = ' . $section);
        }
        // Фильтруем по типу действия.
        $action = $this->getState('filter.action');
        if (!empty($action) && $action != '') {
            $action = $db->quote($db->escape($action, true), false);
            $query->where('`a`.`action` = ' . $action);
        }
        // Фильтруем по дате.
        $dat = $this->getState('filter.dat');
        if (!empty($dat))
        {
            $dat = $this->_db->quote($db->escape($dat));
            $query->where("DATE_FORMAT(`a`.`dat`,'%Y-%m-%d') = " . $dat);
        }
        else
        {
            $query->where("DATE_FORMAT(`a`.`dat`,'%Y-%m-%d') = CURRENT_DATE");
        }

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering', 'a.dat');
        $orderDirn = $this->state->get('list.direction', 'desc');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $items = parent::getItems();
        $result = array();
        foreach ($items as $item) {
            $arr['id'] = $item->id;
            $user = JFactory::getUser();
            $url = JRoute::_("index.php?option=com_projects&amp;task=event.edit&amp;id={$item->id}");
            $dat = JFactory::getDate($item->dat);
            $arr['dat'] = JHtml::link($url, $dat->format("d.m.Y H:i"));
            $arr['section'] = ProjectsHelper::getEventSection($item->section);
            $arr['action'] = ProjectsHelper::getEventAction($item->action);
            $arr['manager'] = $item->manager;
            $arr['itemID'] = $item->itemID;
            $result[] = $arr;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = null, $direction = null)
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager');
        $this->setState('filter.manager', $manager);
        $section = $this->getUserStateFromRequest($this->context . '.filter.section', 'filter_section');
        $this->setState('filter.section', $section);
        $action = $this->getUserStateFromRequest($this->context . '.filter.action', 'filter_action');
        $this->setState('filter.action', $action);
        $dat = $this->getUserStateFromRequest($this->context . '.filter.dat', 'filter_dat');
        $this->setState('filter.dat', $dat);
        parent::populateState('a.dat', 'desc');
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.manager');
        $id .= ':' . $this->getState('filter.section');
        $id .= ':' . $this->getState('filter.action');
        $id .= ':' . $this->getState('filter.dat');
        return parent::getStoreId($id);
    }
}