<?php
/**
 * This class loads a tpl file (template file) and replaces its placeholder with content
 *
 * @author Michael Bartel
 */
class Template {

    private $content;
    private $vars;
    private $blockData;
    private $currentBlockKey;

    public function __construct($tpl_filename) {
        if (file_exists($tpl_filename)) {
            $filename = $tpl_filename;
        } else {
            $filename = WP_PLUGIN_DIR . '/simpleticker/' . $tpl_filename;
        }
        $this->vars = array();
        $this->blockData = array();
        if (file_exists($filename)) {
            $this->content = file_get_contents($filename);
        } else {
            $this->content = null;
            echo "Template file $filename not found!";
        }
    }

    public function assign($key, $value) {
        $this->vars['{' . $key . '}'] = $value;
    }

    public function assignBlock($blockname, $keyValueArr) {
        $this->blockData[$blockname] = $keyValueArr;
    }

    private function parseBlockCallback($match) {
        /*
         * get the data array for the current block
         */
        $currentBlockData = $this->blockData[$this->currentBlockKey];
        /*
         * remove the start and end block
         */
        $content = str_replace(array('{' . $this->currentBlockKey . '}', '{/' . $this->currentBlockKey . '}'), array('', ''), $match[0]);
        $blockContent = '';
        foreach ($currentBlockData as $blockData) {
            $blockKeys = array();
            foreach (array_keys($blockData) as $key) {
                $blockKeys[] = '{' . $key . '}';
            }
            $blockContent .= str_replace($blockKeys, array_values($blockData), $content);
        }
        return $blockContent;
    }

    public function fetch() {
        /*
         * only parse the content, if content is available
         */
        if ($this->content != null) {
            /*
             * first parse the blocks
             */
            foreach (array_keys($this->blockData) as $blockKey) {
                $this->currentBlockKey = $blockKey;
                $regex = '/\{' . $blockKey . '\}(\n|\r|.)*\{\/' . $blockKey . '\}/';
                $this->content = preg_replace_callback($regex, 'Template::parseBlockCallback', $this->content);
            }
            /*
             * now parse the 'simple' variables
             */
            $this->content = str_replace(array_keys($this->vars), array_values($this->vars), $this->content);
        }
        return $this->content;
    }

    public function display() {
        echo $this->fetch();
    }
}

?>
