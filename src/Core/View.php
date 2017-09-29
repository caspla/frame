<?php
namespace Core;

/**
 * Class View
 * @package Core
 * @author Pascal Frey
 */
class View
{
    protected $viewsDirectory = 'templates';
    protected $layoutDirectory = 'templates/layouts';
    protected $layoutFile = 'bootstrap.phtml';
    protected $viewFile;
    protected $viewOutput;

    public function __construct($file)
    {
        $this->viewFile = $file;
    }

    /**
     * Render
     * @return string
     */
    public function render()
    {
        // render view file
        ob_start();
        include $this->viewsDirectory . '/' . $this->viewFile;
        $this->viewOutput = ob_get_clean();

        if ($this->layoutFile)
        {
            // render and return layout with inserted view content
            ob_start();
            include $this->layoutDirectory . '/' . $this->layoutFile;
            return ob_get_clean();
        }
        else
        {
            // return view content without layout.
            return $this->viewOutput;
        }
    }
}