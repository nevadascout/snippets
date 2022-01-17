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

    private function generateState()
    {
        $factory = new \RandomLib\Factory;
        $generator = $factory->getMediumStrengthGenerator();

        // Generate a random string to use
        return $generator->generateString(
            25,
            "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890"
        );
    }

    private function httpPost($url, $data)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
