
<?php
class Response
{
    public static function json(
        $success,
        $message = '',
        $data = [],
        $status = 200,
        $errors = null,
        $meta = []
    ) {
        header('Content-Type: application/json');
        http_response_code($status);

        $payload = [
            'success'     => (bool) $success,
            'status_code' => (int) $status,
            'message'     => (string) $message,
            'data'        => $data ?? [],
            'errors'      => $errors,
            'meta'        => array_merge(
                [
                    'timestamp' => gmdate('c'),
                ],
                (array) $meta
            ),
        ];

        echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        exit;
    }
}

