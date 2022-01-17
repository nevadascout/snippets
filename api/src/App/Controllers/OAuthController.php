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
        if (isset($_GET["error"])) {
            return;
        }

        if (isset($_GET["state"]) && isset($_GET["code"])) {
            $state = $_GET["state"];
            $code = $_GET["code"];

            if (isset($_SESSION["GITHUB_AUTH_STATE"])) {
                $savedState = $_SESSION["GITHUB_AUTH_STATE"];
                unset($_SESSION["GITHUB_AUTH_STATE"]);

                if ($state === $savedState) {
                    $postData = array(
                        "client_id" => $_ENV["GITHUB_CLIENT_ID"],
                        "client_secret" => $_ENV["GITHUB_CLIENT_SECRET"],
                        "code" => $code,
                        "redirect_uri" => $_ENV["OAUTH_REDIRECT_URI"]
                    );

                    $response = $this->httpPost("https://github.com/login/oauth/access_token", $postData);

                    return;
                }
            }
        }

        // @todo: redirect to failure page
    }

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
