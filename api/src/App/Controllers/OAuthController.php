<?php

namespace App\Controllers;

class OAuthController extends BaseController
{
    public function goToGithub()
    {
        // Save the state for comparison later
        $_SESSION["GITHUB_AUTH_STATE"] = $this->generateState();

        $redirectUrl = "https://github.com/login/oauth/authorize?client_id={$_ENV["GITHUB_CLIENT_ID"]}&state={$_SESSION["GITHUB_AUTH_STATE"]}&redirect_uri={$_ENV["OAUTH_REDIRECT_URI"]}";

        header("Location: {$redirectUrl}");
        exit();
    }

    public function callback()
    {
    }
}
