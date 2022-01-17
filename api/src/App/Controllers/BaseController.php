<?php

namespace App\Controllers;

class BaseController
{
    /**
     * Send an error response with a custom error code (default 400)
     *
     * @param $data The response data
     * @param $code The response HTTP code
     */
    public function respondError($data, $code = 400)
    {
        http_response_code($code);
        $this->respond($data);
    }

    /**
     * Convert an array into a string and echo it
     *
     * @param $data The response data
     */
    public function respond($data)
    {
        header("Content-Type: application/json; charset=utf-8");
        $this->respondRaw(json_encode($data));
    }

    /**
     * Respond with a string
     *
     * @param $response The response message
     */
    public function respondRaw($response)
    {
        echo $response;
    }
}
