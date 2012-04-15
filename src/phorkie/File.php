<?php
namespace phorkie;

class File
{
    /**
     * Full path to the file
     *
     * @var string
     */
    public $path;

    /**
     * Repository this file belongs to
     *
     * @var string
     */
    public $repo;

    public function __construct($path, Repository $repo = null)
    {
        $this->path = $path;
        $this->repo = $repo;
    }

    /**
     * Get filename relative to the repository path
     *
     * @return string
     */
    public function getFilename()
    {
        return basename($this->path);
    }

    /**
     * Return the full path to the file
     *
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * Get file extension without dot
     *
     * @return string
     */
    public function getExt()
    {
        return substr($this->path, strrpos($this->path, '.') + 1);
    }

    public function getContent()
    {
        return file_get_contents($this->path);
    }

    public function getRenderedContent(Tool_Result $res = null)
    {
        $ext   = $this->getExt();
        $class = '\\phorkie\\Renderer_Unknown';

        if (isset($GLOBALS['phorkie']['languages'][$ext]['renderer'])) {
            $class = $GLOBALS['phorkie']['languages'][$ext]['renderer'];
        } else if (isset($GLOBALS['phorkie']['languages'][$ext]['mime'])) {
            $type = $GLOBALS['phorkie']['languages'][$ext]['mime'];
            if (substr($type, 0, 5) == 'text/') {
                $class = '\\phorkie\\Renderer_Geshi';
            } else if (substr($type, 0, 6) == 'image/') {
                $class = '\\phorkie\\Renderer_Image';
            }
        }

        $rend = new $class();
        return $rend->toHtml($this, $res);
    }

    /**
     * Get a link to the file
     *
     * @param string $type Link type. Supported are:
     *                     - "raw"
     *                     - "tool"
     * @param string $option
     *
     * @return string
     */
    public function getLink($type, $option = null)
    {
        if ($type == 'raw') {
            return '/' . $this->repo->id . '/raw/' . $this->getFilename();
        } else if ($type == 'tool') {
            return '/' . $this->repo->id . '/tool/' . $option . '/' . $this->getFilename();
        }
        throw new Exception('Unknown type');
    }

    public function getMimeType()
    {
        $ext = $this->getExt();
        if (!isset($GLOBALS['phorkie']['languages'][$ext])) {
            return null;
        }
        return $GLOBALS['phorkie']['languages'][$ext]['mime'];
    }

    /**
     * @return array Array of Tool_Info objects
     */
    public function getToolInfos()
    {
        $tm = new Tool_Manager();
        return $tm->getSuitable($this);
    }

    /**
     * Tells if the file contains textual content and is editable.
     *
     * @return boolean
     */
    public function isText()
    {
        $ext = $this->getExt();
        if (!isset($GLOBALS['phorkie']['languages'][$ext]['mime'])) {
            return false;
        }

        $type = $GLOBALS['phorkie']['languages'][$ext]['mime'];
        return substr($type, 0, 5) === 'text/';
    }
}

?>