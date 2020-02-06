<?php
defined('_JEXEC') or die;

use Joomla\CMS\MVC\Model\ListModel;
use Joomla\CMS\MVC\Model\AdminModel;

class ProjectsModelExhibitors extends ListModel
{
    public function __construct(array $config)
    {
        if (empty($config['filter_fields'])) {
            $config['filter_fields'] = array(
                'e.id',
                'title',
                'projectinactive',
                'projectactive',
                'search',
                'activity',
                'city',
                'status',
                'manager',
            );
        }
        parent::__construct($config);
    }

    protected function _getListQuery()
    {
        $db =& $this->getDbo();
        $query = $db->getQuery(true);
        // Фильтруем проектам, в которых экспонент не учавствует
        $projectinactive = $this->getState('filter.projectinactive');
        // Фильтруем проектам, в которых экспонент учавствует
        $projectactive = $this->getState('filter.projectactive');

        $format = JFactory::getApplication()->input->getString('format', 'html');

        if (!is_numeric($projectactive) && !is_numeric($projectinactive)) {
            $query->select("`e`.`id`, `e`.`is_contractor`");
        }
        else {
            $query->select("DISTINCT `e`.`id`, `e`.`is_contractor`");
        }

        $query
            ->select('trim(IFNULL(`e`.`title_ru_short`,IFNULL(`e`.`title_ru_full`,`e`.`title_en`))) as `title`, u.name as manager')
            ->select("`r`.`name` as `city`")
            ->from("`#__prj_exp` as `e`")
            ->leftJoin("`#__prj_user_action_log` l on e.id = l.itemID and l.action = 'add' and l.section = 'exhibitor'")
            ->leftJoin("`#__users` u on u.id = l.userID")
            ->leftJoin("`#__prj_exp_bank` as `b` ON `b`.`exbID` = `e`.`id`")
            ->leftJoin("`#__grph_cities` as `r` ON `r`.`id` = `e`.`regID`");

        // Фильтруем проектам, в которых экспонент не учавствует
        if (is_numeric($projectinactive)) {
            $query
                ->leftJoin("`#__prj_contracts` as `c` on `c`.`expID` = `e`.`id` and `c`.`prjID` = {$projectinactive}")
                ->where("`c`.`id` is null");
        }
        // Фильтруем проектам, в которых экспонент учавствует
        if (is_numeric($projectactive)) {
            $query
                ->leftJoin("`#__prj_contracts` as `c1` on `c1`.`expID` = `e`.`id`")
                ->where("`c1`.`id` is not null")
                ->where("`c1`.`prjID` = {$projectactive}");
        }

        // Фильтруем по названию
        $text = JFactory::getApplication()->input->getString('text', '');
        $search = $this->getState('filter.search');
        if (!empty($search) && $text == '') $text = $search;
        if ($text != '')
        {
            if (stripos($text, 'id:') === false) {
                $text = $db->q("%{$text}%");
                $query->where("(`title_ru_full` LIKE {$text} OR `title_ru_short` LIKE {$text} OR `title_en` LIKE {$text} OR `b`.`inn` LIKE {$text})");
            }
            if (stripos($text, 'id:') !== false) {
                $id = explode(":", $text);
                if (is_numeric($id[1])) {
                    $id = $db->q($id[1]);
                    $query->where("e.id = {$id}");
                }
            }
        }
        // Фильтруем по городу.
        $city = $this->getState('filter.city');
        if (is_numeric($city) && $format != 'html') {
            $query->where('`e`.`regID` = ' . (int) $city);
        }
        // Фильтруем по менеджеру.
        $manager = $this->getState('filter.manager');
        if (is_numeric($manager)) {
            $query->where('`u`.`id` = ' . (int) $manager);
        }
        // Фильтруем по статусу (подрядчик / ндп).
        $status = JFactory::getApplication()->input->getInt('status', null) ?? $this->getState('filter.status');
        if (is_numeric($status)) {
            switch ($status)
            {
                case 0:
                    {
                        $query->where("`e`.`is_contractor` = 0");
                        $query->where("`e`.`is_ndp` = 0");
                        break;
                    }
                case 1:
                    {
                        $query->where("`e`.`is_contractor` = 1");
                        break;
                    }
                case 2:
                    {
                        $query->where("`e`.`is_ndp` = 1");
                        break;
                    }
            }
        }
        //Фильтр по глобальному проекту
        /*$project = ProjectsHelper::getActiveProject();
        if (is_numeric($project)) {
            $query->where("`e`.`id` IN (SELECT DISTINCT `expID` FROM `#__prj_contracts` WHERE `prjID` = {$project})");
        }*/
        // Фильтруем по видам деятельности.
        $act = $this->getState('filter.activity');
        if (is_numeric($act)) {
            $exponents = ProjectsHelper::getExponentsInActivities($act);
            if (!empty($exponents)) {
                $exponents = implode(', ', $exponents);
                $query->where("`e`.`id` IN ({$exponents})");
            }
            else
            {
                $query->where("`e`.`id` IN (-1)");
            }
        }
        // Фильтруем по ИНН (для поиска синонимов)
        $inn = JFactory::getApplication()->input->getInt('inn', 0);
        if ($inn !== 0)
        {
            $query->where("`b`.`inn` LIKE {$inn}");
        }

        /* Сортировка */
        $orderCol = $this->state->get('list.ordering');
        $orderDirn = $this->state->get('list.direction');
        $query->order($db->escape($orderCol . ' ' . $orderDirn));

        return $query;
    }

    public function getItems()
    {
        $format = JFactory::getApplication()->input->getString('format', 'html');
        $items = parent::getItems();
        $return = base64_encode("index.php?option=com_projects&view=exhibitors");
        $result = array();
        $projectinactive = $this->getState('filter.projectinactive');
        $projectactive = $this->getState('filter.projectactive');
        if (is_numeric($projectinactive))
        {
            $model = AdminModel::getInstance('Project', 'ProjectsModel');
            $project = $model->getItem($projectinactive);
        }
        foreach ($items as $item) {
            $title = $item->title;
            $arr['id'] = $item->id;
            $url = JRoute::_("index.php?option=com_projects&amp;task=exhibitor.edit&amp;id={$item->id}");
            $link = JHtml::link($url, $title);
            $arr['region'] = $item->city;
            $arr['manager'] = $item->manager ?? JText::sprintf('COM_PROJECTS_HEAD_MANAGER_UNKNOWN');
            $arr['style'] = ($item->manager != null) ? '' : "font-style: italic; font-size: 0.8em;";
            $arr['title'] = ($format != 'html') ? $title : $link;
            if (is_numeric($projectinactive))
            {
                $url = JRoute::_("index.php?option=com_projects&amp;task=contract.add&amp;exhibitorID={$item->id}&amp;projectID={$projectinactive}&amp;return={$return}");
                $arr['contract'] = JHtml::link($url, JText::sprintf('COM_PROJECTS_TITLE_NEW_CONTRACT_WITH_PROJECT', $project->title_ru));
            }
            if (is_numeric($projectactive))
            {
                $url = JRoute::_("index.php?option=com_projects&amp;view=contracts&amp;exhibitorID={$item->id}&amp;projectID={$projectactive}");
                $arr['contracts'] = JHtml::link($url, JText::sprintf('COM_PROJECTS_GO_FIND_CONTRACTS'));
            }
            $result[] = $arr;
        }
        return $result;
    }

    /* Сортировка по умолчанию */
    protected function populateState($ordering = 'title', $direction = 'asc')
    {
        $search = $this->getUserStateFromRequest($this->context . '.filter.search', 'filter_search');
        $this->setState('filter.search', $search);
        $activity = $this->getUserStateFromRequest($this->context . '.filter.activity', 'filter_activity', '', 'string');
        $this->setState('filter.state', $activity);
        $city = $this->getUserStateFromRequest($this->context . '.filter.city', 'filter_city', '', 'string');
        $this->setState('filter.city', $city);
        $projectinactive = $this->getUserStateFromRequest($this->context . '.filter.projectinactive', 'filter_projectinactive', '', 'string');
        $this->setState('filter.projectinactive', $projectinactive);
        $projectactive = $this->getUserStateFromRequest($this->context . '.filter.projectactive', 'filter_projectactive', '', 'string');
        $this->setState('filter.projectactive', $projectactive);
        $status = $this->getUserStateFromRequest($this->context . '.filter.status', 'filter_status', '', 'string');
        $this->setState('filter.status', $status);
        $manager = $this->getUserStateFromRequest($this->context . '.filter.manager', 'filter_manager', '', 'string');
        $this->setState('filter.manager', $manager);
        parent::populateState($ordering, $direction);
    }

    protected function getStoreId($id = '')
    {
        $id .= ':' . $this->getState('filter.search');
        $id .= ':' . $this->getState('filter.activity');
        $id .= ':' . $this->getState('filter.city');
        $id .= ':' . $this->getState('filter.projectinactive');
        $id .= ':' . $this->getState('filter.projectactive');
        $id .= ':' . $this->getState('filter.status');
        $id .= ':' . $this->getState('filter.manager');
        return parent::getStoreId($id);
    }
}