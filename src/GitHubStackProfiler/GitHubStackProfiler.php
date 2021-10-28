<?php

namespace GitHubStackProfiler;

use Exception;

class GitHubStackProfiler
{
    /**
     * GitHub Username.
     *
     * @var string
     */
    private $username;

    /**
     * GitHub Personal Access Token.
     *
     * @var string
     */
    private $token;

    private const BASE_URL = 'https://api.github.com/';

    /**
     * Class Constructor.
     */
    public function __construct(string $username = null, string $token)
    {
        $this->username = $username;
        $this->token = $token;
    }

    public function crunchStack()
    {
    }

    /**
     * CURL Request
     *
     * @param string $endpoint
     *
     * @return string|null
     *
     * @throws \Exception
     */
    private function request(string $endpoint): ?string {
        $ch = curl_init();

        if (!$ch) throw new Exception("Could not initialize CURL.");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        if ($this->token) {
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'Accept: application/vnd.github.v3+json'
            ]);
        }

        curl_setopt(
            $ch,
            CURLOPT_URL,
            self::BASE_URL . $endpoint
        );

        $response = curl_exec($ch);
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        curl_close($ch);

        if ($code == 200) return $response;

        if ($code == 404) return null;

        if ($code <= 199) throw new Exception("A CURL error with code '$code', has occurred.");

        if ($code == 403) throw new Exception("Request returned a forbidden response.");

        throw new Exception("Bad response");
    }
}
