<?php

declare(strict_types=1);


namespace Tests;

use PHPUnit\Framework\TestCase;
use GitHubStackProfiler\GitHubStackProfiler;

$dotenv = \Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

final class AccountsTest extends TestCase
{
    /**
     * Undocumented function
     *
     * @return void
     */
    public function testCanCrunchStack(): void
    {
        $profiler = new GitHubStackProfiler('francis94c', $_ENV['GITHUB_TOKEN']);

        $profiler->crunchStack();
    }
}