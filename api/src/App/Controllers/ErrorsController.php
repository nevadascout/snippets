<?php

namespace App\Controllers;

class ErrorsController extends BaseController
{
    public function notFound()
    {
        $data = array(
            "error" => "The requested endpoint was not found",
            "status_code" => 404
        );

        $this->respondError($data, 404);
    }
}
