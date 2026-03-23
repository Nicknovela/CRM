<?php

namespace Core;

class View
{
    public static function render(string $view, array $data = []): void
    {
        extract($data);
        $appConfig = require BASE_PATH . '/config/app.php';
        $appName   = $appConfig['name'];

        ob_start();
        $viewFile = BASE_PATH . "/views/{$view}.php";
        if (!file_exists($viewFile)) {
            ob_end_clean();
            http_response_code(500);
            die("View [{$view}] not found.");
        }
        include $viewFile;
        $content = ob_get_clean();

        $layout = $layout ?? 'app';
        $layoutFile = BASE_PATH . "/views/layouts/{$layout}.php";
        if (file_exists($layoutFile)) {
            include $layoutFile;
        } else {
            echo $content;
        }
    }

    public static function json(mixed $data, int $status = 200): never
    {
        http_response_code($status);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        exit;
    }

    public static function partial(string $view, array $data = []): string
    {
        extract($data);
        ob_start();
        include BASE_PATH . "/views/{$view}.php";
        return ob_get_clean();
    }
}
