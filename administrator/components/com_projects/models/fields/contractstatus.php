<?php
defined('_JEXEC') or die;
jimport('joomla.form.helper');
JFormHelper::loadFieldClass('list');

class JFormFieldContractstatus extends JFormFieldList
{
    protected $type = 'Contractstatus';
    protected $loadExternally = 0;

    protected function getOptions()
    {
        $input = JFactory::getApplication()->input;
        $id = $input->getInt('id', 0);
        $view = $input->getString('view', '');
        $db =& JFactory::getDbo();
        $query = $db->getQuery(true);
        $query
            ->select("`id`, `code`, `title`, `weight`")
            ->from('#__prj_statuses')
            ->order("`weight`");
        if ($view === 'contract' && $id === 0) $query->where('`code` <> 4');
        $result = $db->setQuery($query)->loadObjectList();

        $options = array();

        foreach ($result as $item) {
            $options[] = JHtml::_('select.option', $item->code, JText::sprintf($item->title));
        }

        if (!$this->loadExternally) {
            $options = array_merge(parent::getOptions(), $options);
        }

        return $options;
    }

    public function getOptionsExternally()
    {
        $this->loadExternally = 1;
        return $this->getOptions();
    }
}