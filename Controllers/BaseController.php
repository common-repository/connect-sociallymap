<?php

namespace SociallymapConnect\Controllers;

use SociallymapConnect\Includes\BaseView;

abstract class BaseController
{
    /**
     * @param string $route
     * @param array $args
     * @return mixed
     */
    public static function forward($route, array $args = [])
    {
        $routeItems = explode('::', $route);
        $controllerClass = 'SociallymapConnect\Controllers\\' . $routeItems[0];
        $controller = new $controllerClass();
        $actionName = $routeItems[1];

        return call_user_func_array([$controller, $actionName], $args);
    }

    /**
     * @param int $errorCode
     * @param string $errorMessage
     * @return void
     */
    public static function forwardError($errorCode, $errorMessage)
    {
        switch ($errorCode) {
            case 400:
                $headerMessage = 'Bad Request';
                break;
            case 404:
                $headerMessage = 'Not Found';
                break;
            default:
                $headerMessage = 'Error';
        }

        header(sprintf('HTTP/1.0 %d %s', $errorCode, $headerMessage));
        $body = [
            'code' => $errorCode,
            'error' => $errorMessage
        ];
        $content = \json_encode($body);

        header('Content-type: application/json');
        header('Content-length: ' . strlen($content));
        echo $content;
        exit();
    }

    /**
     * @param string $pageSlug
     */
    public static function redirectAdmin($pageSlug)
    {
        $url = admin_url('admin.php?page=' . $pageSlug);
        {
            if (!headers_sent())
            {
                header('Location: '.$url);
                exit;
            }

            echo '<script type="text/javascript">';
            echo 'window.location.href="' . $url . '";';
            echo '</script>';
            echo '<noscript>';
            echo '<meta http-equiv="refresh" content="0;url=' . $url . '" />';
            echo '</noscript>';
            exit;
        }

    }

    /**
     * @param string $viewName
     * @param array $data
     * @return string
     */
    public static function render($viewName, array $data = [])
    {
        $view = new BaseView($viewName);

        return $view->render($data);
    }
}
