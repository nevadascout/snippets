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
            error_log("GitHub login failed: {$_GET["error"]}");

            // @todo: redirect to error page
            return;
        }

        if (isset($_GET["state"]) && isset($_GET["code"])) {
            $state = $_GET["state"];
            $code = $_GET["code"];

            if (!isset($_SESSION["GITHUB_AUTH_STATE"])) {
                error_log("GitHub login failed: no saved state");

                // @todo: redirect to failure page
                return;
            }

            $savedState = $_SESSION["GITHUB_AUTH_STATE"];
            unset($_SESSION["GITHUB_AUTH_STATE"]);

            if ($state !== $savedState) {
                error_log("GitHub login failed: states don't match");

                // @todo: redirect to failure page
                return;
            }

            $postData = array(
                "client_id" => $_ENV["GITHUB_CLIENT_ID"],
                "client_secret" => $_ENV["GITHUB_CLIENT_SECRET"],
                "code" => $code,
                "redirect_uri" => $_ENV["OAUTH_REDIRECT_URI"]
            );

            $postHeaders = array("Accept: application/json");
            $postResponse = $this->httpPost("https://github.com/login/oauth/access_token", $postData, $postHeaders);
            $postResponse = json_decode($postResponse, true);

            if (isset($postResponse["access_token"])) {
                $_SESSION["access_token"] = $postResponse["access_token"];
                $_SESSION["authenticated"] = true;

                $getHeaders = array(
                    "Authorization: token {$_SESSION["access_token"]}",
                    "User-Agent: SnippetsApp"
                );
                $getResponse = $this->httpGet("https://api.github.com/user", $getHeaders);
                $getResponse = json_decode($getResponse, true);

                var_dump($getResponse);

                // @todo: save/update user data in DB:
                //  id (from github)
                //  login
                //  name
                //  avatar_url

                // @todo: redirect to success page

                return;
            }
        }

        error_log("GitHub login failed");

        // @todo: redirect to failure page
        echo "something went wrong";
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

    private function httpPost($url, $data, $headers = null)
    {
        $curl = curl_init($url);

        curl_setopt($curl, CURLOPT_POST, true);
        curl_setopt($curl, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }

    private function httpGet($url, $headers = null)
    {
        $curl = curl_init($url);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);

        if ($headers) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return $response;
    }
}
