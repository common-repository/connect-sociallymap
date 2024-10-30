<?php

namespace SociallymapConnect\Includes;

class BaseView
{
    /**
     * @var string
     */
    protected $viewName;

    /**
     * @param string $viewName
     */
    public function __construct($viewName)
    {
        $this->viewName = $viewName;
    }

    /**
     * @param array $data
     * @return string
     */
    public function render(array $data)
    {
        $viewPath = sprintf('%s/../views/%s.php', __DIR__, $this->viewName);

        foreach ($data as $key => $value) {
            $$key = $value;
        }

        ob_start();
        require $viewPath;

        return ob_get_clean();
    }

    /**
     * @param string $name
     * @param array $data
     */
    public function includeView($name, $data = [])
    {
        $view = new BaseView($name);
        $response = $view->render($data);

        echo $response;
    }
}
