<?php
use Joomla\CMS\MVC\Model\BaseDatabaseModel;

defined('_JEXEC') or die;

class ProjectsModelSync extends BaseDatabaseModel
{
    public function __construct($config = array())
    {
        parent::__construct($config);
    }


    /**
     * Выполняет сохранение информации о статусах сделок по менеджерам на текущий день
     * @since 2.0.1-alpha.2
     */
    public function saveStatuses(): void
    {
        $db = $this->getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("count(id)")
            ->from("#__prj_managers_stat")
            ->where("`dat` = curdate()");
        $result = $db->setQuery($query)->loadResult();
        if ((int) $result > 0) return;
        $query = "call s7vi9_prj_save_manager_stat()";
        $db->setQuery($query)->execute();
    }

}
