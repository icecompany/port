<?php
defined('_JEXEC') or die;
use Joomla\CMS\MVC\View\HtmlView;
use Joomla\CMS\HTML\HTMLHelper;

class ProjectsViewExhibitor extends HtmlView {
    protected $item, $form, $script, $id, $history, $coExhibitors, $persons, $children, $r_script;

    public function display($tmp = null) {
        $this->id = JFactory::getApplication()->input->getInt('id', 0);
        $this->form = $this->get('Form');
        $this->item = $this->get('Item');
        $this->script = $this->get('Script');
        $this->r_script = $this->get('RegionScript');
        $this->history = $this->get('History');
        $this->coExhibitors = $this->get('CoExhibitors');
        $this->persons = $this->get('Persons');
        $this->children = ($this->item->id != null) ? $this->get('Children') : array();

        $this->addToolbar();
        $this->setDocument();

        parent::display($tpl);
    }

    protected function addToolbar() {
        //JFactory::getApplication()->input->set('hidemainmenu', true);
        $title = ($this->id != 0) ? ProjectsHelper::getExpTitle($this->item->title_ru_short, $this->item->title_ru_full, $this->item->title_en) : JText::sprintf('COM_PROJECTS_TITLE_NEW_COMPANY');

        JToolbarHelper::title($title, '');
	    JToolBarHelper::apply('exhibitor.apply', 'JTOOLBAR_APPLY');
        JToolbarHelper::save('exhibitor.save', 'JTOOLBAR_SAVE');
        JToolbarHelper::cancel('exhibitor.cancel', 'JTOOLBAR_CLOSE');
    }

    protected function setDocument() {
        JHtml::_('bootstrap.framework');
    }
}