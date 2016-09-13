<?php

/**
 * @package   	JCE
 * @copyright 	Copyright (c) 2009-2016 Ryan Demmer. All rights reserved.
 * @license   	GNU/GPL 2 or later - http://www.gnu.org/licenses/old-licenses/gpl-2.0.html
 * JCE is free software. This version may have been modified pursuant
 * to the GNU General Public License, and as distributed it includes or
 * is derivative of works licensed under the GNU General Public License or
 * other free or open source software licenses.
 */
defined('_JEXEC') or die('RESTRICTED');

final class WFTabs extends JObject {

    private $_tabs = array();
    private $_panels = array();
    private $_paths = array();

    /**
     * Constructor activating the default information of the class
     * @access  public
     */
    public function __construct($config = array()) {
        if (!array_key_exists('base_path', $config)) {
            $config['base_path'] = WF_EDITOR_LIBRARIES;
        }

        $this->setProperties($config);

        if (array_key_exists('template_path', $config)) {
            $this->addTemplatePath($config['template_path']);
        } else {
            $this->addTemplatePath($this->get('base_path') . '/tmpl');
        }
    }

    /**
     * Returns a reference to a WFTabs object
     *
     * This method must be invoked as:
     *    <pre>  $tabs = WFTabs::getInstance();</pre>
     *
     * @access  public
     * @return  object WFTabs
     */
    public static function getInstance($config = array()) {
        static $instance;

        if (!is_object($instance)) {
            $instance = new WFTabs($config);
        }

        return $instance;
    }

    /**
     * Add a template path
     * @access 	public
     * @param 	string $path
     */
    public function addTemplatePath($path) {
        $this->_paths[] = $path;
    }

    /**
     * Load a panel view
     * @access  private
     * @param object $layout Layout (panel) name
     * @return panel JView object
     */
    private function loadPanel($panel) {
        $view = new WFView(array(
          'name'    => $panel,
          'layout'  => $panel
        ));

        // add tab paths
        foreach ($this->_paths as $path) {
            $view->addTemplatePath($path);
        }

        return $view;
    }

    public function getPanel($panel) {
        if (array_key_exists($panel, $this->_panels)) {
            return $this->_panels[$panel];
        }

        return false;
    }

    /**
     * Add a tab to the document. A panel is automatically created and assigned
     * @access	public
     * @param object $tab Tab name
     * @param int $state Tab state (active or inactive)
     * @param array $values An array of values to assign to panel view
     */
    public function addTab($tab, $state = 1, $values = array()) {
        // backwards compatability as $state has been removed
        if (!$state) {
            return false;
        }

        if (!array_key_exists($tab, $this->_tabs)) {
            $this->_tabs[$tab] = $tab;
            $panel = $this->addPanel($tab);

            // array is not empty and is associative
            if (!empty($values) && array_values($values) !== $values) {
               $panel->assign($values);
            }
        }
    }

    /**
     * Add a panel to the document
     * @access	public
     * @param 	object $panel Panel name
     */
    public function addPanel($tab) {
        if (!array_key_exists($tab, $this->_panels)) {
            $this->_panels[$tab] = $this->loadPanel($tab);

            return $this->_panels[$tab];
        }
    }

    /**
     * Remove a tab from the document
     * @access	public
     * @param object $tab Tab name
     */
    public function removeTab($tab) {
        if (array_key_exists($tab, $this->_tabs)) {
            unset($this->_tabs[$tab]);
        }
    }

    /**
     * Render the document tabs and panels
     * @access	public
     */
    public function render() {
        $output = "";

        if (!empty($this->_tabs)) {
            $output .= '<div id="tabs">';
        }

        // add tabs
        if (count($this->_tabs) > 1) {
            $output .= '<ul class="uk-tab">' . "\n";

            $x = 0;

            foreach ($this->_tabs as $tab) {
                $class = "";

                if ($x === 0) {
                    $class .= " uk-active";
                }

                $output .= "\t" . '<li class="' . $class . '"><a href="#' . $tab . '_tab">' . WFText::_('WF_TAB_' . strtoupper($tab)) . '</a></li>' . "\n";
                $x++;
            }

            $output .= "</ul>\n";
        }

        // add panels
        if (!empty($this->_panels)) {
            $x = 0;

            $output .= '<div class="uk-switcher">';

            foreach ($this->_panels as $key => $panel) {
                $class = "";

                if (!empty($this->_tabs)) {

                    if ($x === 0) {
                        $class .= " uk-active";
                    } else {
                        $class .= " uk-tabs-hide";
                    }

                    $output .= '<div id="' . $key . '_tab" class="' . $class . '">';
                    $output .= $panel->loadTemplate();
                    $output .= '</div>';
                } else {
                    $output .= '<div id="' . $key . '">';
                    $output .= $panel->loadTemplate();
                    $output .= '</div>';
                }
                $x++;
            }

            $output .= '</div>';
        }

        // add closing div
        if (!empty($this->_tabs)) {
            $output .= "</div>\n";
        }

        echo $output;
    }

}

?>
