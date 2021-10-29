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
    public function __construct(string $username = null, string $token = null)
    {
        $this->username = $username;
        $this->token = $token;
    }

    public function crunchStack()
    {
        $count = 100;
        $repos = [];
        $page = 1;

        // Get all repositories.
        while ($count > 0) {
            $response = json_decode($this->request($this->token ? 'user/repos' : "users/$this->username/repos", [
                'page' => $page,
                'per_page' => 100
            ]));
            if ($response) {
                $repos = array_merge($repos, $response);
                $count = count($response);
                ++$page;
            } else {
                $count = 0;
            }
        }

        $lines = [];

        // $max = max($languages);
        //         $key = array_search($max, $languages);
        //         if ($languages[$key] == "Dart") {

        foreach ($repos as $repo) {
            $languages = json_decode($this->request($repo->languages_url, null, false));
            if ($languages) {
                foreach ($languages as $language => $count) {
                    $lines[$language] = ($lines[$language] ?? 0) + $count;
                }
            }
        }

        $values = array_values($lines);
        $keys = array_keys($lines);
        array_multisort($values, SORT_DESC, $keys);

        $total = array_sum($values);

        $stack = [];

        foreach ($keys as $key) {
            $stack[$key] = round($lines[$key] / $total * 100, 2);
        }

        var_dump($stack);
    }

    /**
     * CURL Request
     *
     * @param string $endpoint
     * @param array|null $query
     *
     * @return string|null
     *
     * @throws \Exception
     */
    private function request(string $endpoint, ?array $query = null, bool $useBaseUrl = true): ?string
    {
        $ch = curl_init();

        if (!$ch) throw new Exception("Could not initialize CURL.");

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);

        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'User-Agent: GitHubStackProfiler',
            'Accept: application/vnd.github.v3+json'
        ]);

        if ($this->token) {
            curl_setopt($ch, CURLOPT_USERPWD, $this->username . ":" . $this->token);
        }

        curl_setopt(
            $ch,
            CURLOPT_URL,
            ($useBaseUrl ? self::BASE_URL : '') . $endpoint . ($query ? '?' . http_build_query($query) : '')
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
