<?php

declare(strict_types=1);

namespace Tests\Integration\Infrastructure\VCS\Github;

use Slub\Domain\Entity\PR\MessageIdentifier;
use Slub\Domain\Entity\PR\PR;
use Slub\Domain\Entity\PR\PRIdentifier;
use Slub\Domain\Repository\PRRepositoryInterface;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Tests\Integration\Infrastructure\WebTestCase;

/**
 * @author    Samir Boulil <samir.boulil@akeneo.com>
 */
class NewEventActionTest extends WebTestCase
{
    /**
     * @test
     */
    public function it_successfully_processes_supported_events()
    {
        $this->createDefaultPR();
        $client = static::createClient();
        $signature = sprintf('sha1=%s', hash_hmac('sha1', $this->getPayload(), $this->get('GITHUB_WEBHOOK_SECRET')));
        $client->request('POST', '/vcs/github', [], [], [
            'HTTP_X-GitHub-Event' => 'pull_request_review',
            'HTTP_X-Hub-Signature' => $signature,
        ], $this->getPayload());
        $this->assertEquals(200, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_if_the_signatures_does_not_match()
    {
        $this->expectException(BadRequestHttpException::class);
        $client = static::createClient();
        $client->request('POST', '/vcs/github', [], [], [
            'HTTP_X-GitHub-Event' => 'SUPPORTED_EVENT_TYPE',
            'HTTP_X-Hub-Signature' => hash_hmac('sha1', $this->getPayload(), 'wrong_secret'),
        ], $this->getPayload());
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    /**
     * @test
     */
    public function it_throws_if_the_event_is_unsupported()
    {
        $this->expectException(BadRequestHttpException::class);
        $client = static::createClient();
        $signature = sprintf('sha1=%s', hash_hmac('sha1', $this->getPayload(), $this->get('GITHUB_WEBHOOK_SECRET')));
        $client->request('POST', '/vcs/github', [], [], [
            'HTTP_X-GitHub-Event' => 'WRONG_EVENT_TYPE',
            'HTTP_X-Hub-Signature' => $signature
        ], $this->getPayload());
        $this->assertEquals(400, $client->getResponse()->getStatusCode());
    }

    private function createDefaultPR(): void
    {
        $PRRepository = $this->get('slub.infrastructure.persistence.pr_repository');
        $PRRepository->save(
            PR::create(
                PRIdentifier::create('SamirBoulil/slub/10'),
                MessageIdentifier::create('CHANNEL_ID@1111')
            )
        );
    }

    private function getPayload(): string
    {
        $json = <<<JSON
{
    "action": "submitted",
    "review": {
        "id": 207149777,
        "node_id": "MDE3OlB1bGxSZXF1ZXN0UmV2aWV3MjA3MTQ5Nzc3",
        "user": {
            "login": "SamirBoulil",
            "id": 1826473,
            "node_id": "MDQ6VXNlcjE4MjY0NzM=",
            "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
            "gravatar_id": "",
            "url": "https://api.github.com/users/SamirBoulil",
            "html_url": "https://github.com/SamirBoulil",
            "followers_url": "https://api.github.com/users/SamirBoulil/followers",
            "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
            "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
            "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
            "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
            "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
            "repos_url": "https://api.github.com/users/SamirBoulil/repos",
            "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
            "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
            "type": "User",
            "site_admin": false
        },
        "body": "Pourquoi pas",
        "commit_id": "caed81fdd51f573d7235fb63959ef6a6d0aaf809",
        "submitted_at": "2019-02-24T12:33:40Z",
        "state": "approved",
        "html_url": "https://github.com/SamirBoulil/slub/pull/10#pullrequestreview-207149777",
        "pull_request_url": "https://api.github.com/repos/SamirBoulil/slub/pulls/10",
        "author_association": "OWNER",
        "_links": {
            "html": {
                "href": "https://github.com/SamirBoulil/slub/pull/10#pullrequestreview-207149777"
            },
            "pull_request": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/pulls/10"
            }
        }
    },
    "pull_request": {
        "url": "https://api.github.com/repos/SamirBoulil/slub/pulls/10",
        "id": 255680940,
        "node_id": "MDExOlB1bGxSZXF1ZXN0MjU1NjgwOTQw",
        "html_url": "https://github.com/SamirBoulil/slub/pull/10",
        "diff_url": "https://github.com/SamirBoulil/slub/pull/10.diff",
        "patch_url": "https://github.com/SamirBoulil/slub/pull/10.patch",
        "issue_url": "https://api.github.com/repos/SamirBoulil/slub/issues/10",
        "number": 10,
        "state": "open",
        "locked": false,
        "title": "Add github webhook",
        "user": {
            "login": "SamirBoulil",
            "id": 1826473,
            "node_id": "MDQ6VXNlcjE4MjY0NzM=",
            "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
            "gravatar_id": "",
            "url": "https://api.github.com/users/SamirBoulil",
            "html_url": "https://github.com/SamirBoulil",
            "followers_url": "https://api.github.com/users/SamirBoulil/followers",
            "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
            "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
            "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
            "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
            "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
            "repos_url": "https://api.github.com/users/SamirBoulil/repos",
            "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
            "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
            "type": "User",
            "site_admin": false
        },
        "body": "",
        "created_at": "2019-02-24T12:33:24Z",
        "updated_at": "2019-02-24T12:33:40Z",
        "closed_at": null,
        "merged_at": null,
        "merge_commit_sha": "32ddf907072311c89ee013b4f66040a220d6b424",
        "assignee": null,
        "assignees": [],
        "requested_reviewers": [],
        "requested_teams": [],
        "labels": [],
        "milestone": null,
        "commits_url": "https://api.github.com/repos/SamirBoulil/slub/pulls/10/commits",
        "review_comments_url": "https://api.github.com/repos/SamirBoulil/slub/pulls/10/comments",
        "review_comment_url": "https://api.github.com/repos/SamirBoulil/slub/pulls/comments{/number}",
        "comments_url": "https://api.github.com/repos/SamirBoulil/slub/issues/10/comments",
        "statuses_url": "https://api.github.com/repos/SamirBoulil/slub/statuses/caed81fdd51f573d7235fb63959ef6a6d0aaf809",
        "head": {
            "label": "SamirBoulil:plug-github",
            "ref": "plug-github",
            "sha": "caed81fdd51f573d7235fb63959ef6a6d0aaf809",
            "user": {
                "login": "SamirBoulil",
                "id": 1826473,
                "node_id": "MDQ6VXNlcjE4MjY0NzM=",
                "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
                "gravatar_id": "",
                "url": "https://api.github.com/users/SamirBoulil",
                "html_url": "https://github.com/SamirBoulil",
                "followers_url": "https://api.github.com/users/SamirBoulil/followers",
                "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
                "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
                "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
                "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
                "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
                "repos_url": "https://api.github.com/users/SamirBoulil/repos",
                "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
                "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
                "type": "User",
                "site_admin": false
            },
            "repo": {
                "id": 166291213,
                "node_id": "MDEwOlJlcG9zaXRvcnkxNjYyOTEyMTM=",
                "name": "slub",
                "full_name": "SamirBoulil/slub",
                "private": false,
                "owner": {
                    "login": "SamirBoulil",
                    "id": 1826473,
                    "node_id": "MDQ6VXNlcjE4MjY0NzM=",
                    "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
                    "gravatar_id": "",
                    "url": "https://api.github.com/users/SamirBoulil",
                    "html_url": "https://github.com/SamirBoulil",
                    "followers_url": "https://api.github.com/users/SamirBoulil/followers",
                    "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
                    "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
                    "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
                    "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
                    "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
                    "repos_url": "https://api.github.com/users/SamirBoulil/repos",
                    "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
                    "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
                    "type": "User",
                    "site_admin": false
                },
                "html_url": "https://github.com/SamirBoulil/slub",
                "description": "Improve the feedback loop between Github pull requests statuses and teams using Slack.",
                "fork": false,
                "url": "https://api.github.com/repos/SamirBoulil/slub",
                "forks_url": "https://api.github.com/repos/SamirBoulil/slub/forks",
                "keys_url": "https://api.github.com/repos/SamirBoulil/slub/keys{/key_id}",
                "collaborators_url": "https://api.github.com/repos/SamirBoulil/slub/collaborators{/collaborator}",
                "teams_url": "https://api.github.com/repos/SamirBoulil/slub/teams",
                "hooks_url": "https://api.github.com/repos/SamirBoulil/slub/hooks",
                "issue_events_url": "https://api.github.com/repos/SamirBoulil/slub/issues/events{/number}",
                "events_url": "https://api.github.com/repos/SamirBoulil/slub/events",
                "assignees_url": "https://api.github.com/repos/SamirBoulil/slub/assignees{/user}",
                "branches_url": "https://api.github.com/repos/SamirBoulil/slub/branches{/branch}",
                "tags_url": "https://api.github.com/repos/SamirBoulil/slub/tags",
                "blobs_url": "https://api.github.com/repos/SamirBoulil/slub/git/blobs{/sha}",
                "git_tags_url": "https://api.github.com/repos/SamirBoulil/slub/git/tags{/sha}",
                "git_refs_url": "https://api.github.com/repos/SamirBoulil/slub/git/refs{/sha}",
                "trees_url": "https://api.github.com/repos/SamirBoulil/slub/git/trees{/sha}",
                "statuses_url": "https://api.github.com/repos/SamirBoulil/slub/statuses/{sha}",
                "languages_url": "https://api.github.com/repos/SamirBoulil/slub/languages",
                "stargazers_url": "https://api.github.com/repos/SamirBoulil/slub/stargazers",
                "contributors_url": "https://api.github.com/repos/SamirBoulil/slub/contributors",
                "subscribers_url": "https://api.github.com/repos/SamirBoulil/slub/subscribers",
                "subscription_url": "https://api.github.com/repos/SamirBoulil/slub/subscription",
                "commits_url": "https://api.github.com/repos/SamirBoulil/slub/commits{/sha}",
                "git_commits_url": "https://api.github.com/repos/SamirBoulil/slub/git/commits{/sha}",
                "comments_url": "https://api.github.com/repos/SamirBoulil/slub/comments{/number}",
                "issue_comment_url": "https://api.github.com/repos/SamirBoulil/slub/issues/comments{/number}",
                "contents_url": "https://api.github.com/repos/SamirBoulil/slub/contents/{+path}",
                "compare_url": "https://api.github.com/repos/SamirBoulil/slub/compare/{base}...{head}",
                "merges_url": "https://api.github.com/repos/SamirBoulil/slub/merges",
                "archive_url": "https://api.github.com/repos/SamirBoulil/slub/{archive_format}{/ref}",
                "downloads_url": "https://api.github.com/repos/SamirBoulil/slub/downloads",
                "issues_url": "https://api.github.com/repos/SamirBoulil/slub/issues{/number}",
                "pulls_url": "https://api.github.com/repos/SamirBoulil/slub/pulls{/number}",
                "milestones_url": "https://api.github.com/repos/SamirBoulil/slub/milestones{/number}",
                "notifications_url": "https://api.github.com/repos/SamirBoulil/slub/notifications{?since,all,participating}",
                "labels_url": "https://api.github.com/repos/SamirBoulil/slub/labels{/name}",
                "releases_url": "https://api.github.com/repos/SamirBoulil/slub/releases{/id}",
                "deployments_url": "https://api.github.com/repos/SamirBoulil/slub/deployments",
                "created_at": "2019-01-17T20:21:39Z",
                "updated_at": "2019-02-24T11:35:21Z",
                "pushed_at": "2019-02-24T12:33:25Z",
                "git_url": "git://github.com/SamirBoulil/slub.git",
                "ssh_url": "git@github.com:SamirBoulil/slub.git",
                "clone_url": "https://github.com/SamirBoulil/slub.git",
                "svn_url": "https://github.com/SamirBoulil/slub",
                "homepage": "",
                "size": 211,
                "stargazers_count": 0,
                "watchers_count": 0,
                "language": "PHP",
                "has_issues": true,
                "has_projects": true,
                "has_downloads": true,
                "has_wiki": true,
                "has_pages": false,
                "forks_count": 0,
                "mirror_url": null,
                "archived": false,
                "open_issues_count": 1,
                "license": {
                    "key": "mit",
                    "name": "MIT License",
                    "spdx_id": "MIT",
                    "url": "https://api.github.com/licenses/mit",
                    "node_id": "MDc6TGljZW5zZTEz"
                },
                "forks": 0,
                "open_issues": 1,
                "watchers": 0,
                "default_branch": "master"
            }
        },
        "base": {
            "label": "SamirBoulil:master",
            "ref": "master",
            "sha": "7a5df1cd6070a872fbc22522ceefa83c763c3f6e",
            "user": {
                "login": "SamirBoulil",
                "id": 1826473,
                "node_id": "MDQ6VXNlcjE4MjY0NzM=",
                "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
                "gravatar_id": "",
                "url": "https://api.github.com/users/SamirBoulil",
                "html_url": "https://github.com/SamirBoulil",
                "followers_url": "https://api.github.com/users/SamirBoulil/followers",
                "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
                "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
                "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
                "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
                "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
                "repos_url": "https://api.github.com/users/SamirBoulil/repos",
                "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
                "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
                "type": "User",
                "site_admin": false
            },
            "repo": {
                "id": 166291213,
                "node_id": "MDEwOlJlcG9zaXRvcnkxNjYyOTEyMTM=",
                "name": "slub",
                "full_name": "SamirBoulil/slub",
                "private": false,
                "owner": {
                    "login": "SamirBoulil",
                    "id": 1826473,
                    "node_id": "MDQ6VXNlcjE4MjY0NzM=",
                    "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
                    "gravatar_id": "",
                    "url": "https://api.github.com/users/SamirBoulil",
                    "html_url": "https://github.com/SamirBoulil",
                    "followers_url": "https://api.github.com/users/SamirBoulil/followers",
                    "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
                    "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
                    "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
                    "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
                    "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
                    "repos_url": "https://api.github.com/users/SamirBoulil/repos",
                    "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
                    "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
                    "type": "User",
                    "site_admin": false
                },
                "html_url": "https://github.com/SamirBoulil/slub",
                "description": "Improve the feedback loop between Github pull requests statuses and teams using Slack.",
                "fork": false,
                "url": "https://api.github.com/repos/SamirBoulil/slub",
                "forks_url": "https://api.github.com/repos/SamirBoulil/slub/forks",
                "keys_url": "https://api.github.com/repos/SamirBoulil/slub/keys{/key_id}",
                "collaborators_url": "https://api.github.com/repos/SamirBoulil/slub/collaborators{/collaborator}",
                "teams_url": "https://api.github.com/repos/SamirBoulil/slub/teams",
                "hooks_url": "https://api.github.com/repos/SamirBoulil/slub/hooks",
                "issue_events_url": "https://api.github.com/repos/SamirBoulil/slub/issues/events{/number}",
                "events_url": "https://api.github.com/repos/SamirBoulil/slub/events",
                "assignees_url": "https://api.github.com/repos/SamirBoulil/slub/assignees{/user}",
                "branches_url": "https://api.github.com/repos/SamirBoulil/slub/branches{/branch}",
                "tags_url": "https://api.github.com/repos/SamirBoulil/slub/tags",
                "blobs_url": "https://api.github.com/repos/SamirBoulil/slub/git/blobs{/sha}",
                "git_tags_url": "https://api.github.com/repos/SamirBoulil/slub/git/tags{/sha}",
                "git_refs_url": "https://api.github.com/repos/SamirBoulil/slub/git/refs{/sha}",
                "trees_url": "https://api.github.com/repos/SamirBoulil/slub/git/trees{/sha}",
                "statuses_url": "https://api.github.com/repos/SamirBoulil/slub/statuses/{sha}",
                "languages_url": "https://api.github.com/repos/SamirBoulil/slub/languages",
                "stargazers_url": "https://api.github.com/repos/SamirBoulil/slub/stargazers",
                "contributors_url": "https://api.github.com/repos/SamirBoulil/slub/contributors",
                "subscribers_url": "https://api.github.com/repos/SamirBoulil/slub/subscribers",
                "subscription_url": "https://api.github.com/repos/SamirBoulil/slub/subscription",
                "commits_url": "https://api.github.com/repos/SamirBoulil/slub/commits{/sha}",
                "git_commits_url": "https://api.github.com/repos/SamirBoulil/slub/git/commits{/sha}",
                "comments_url": "https://api.github.com/repos/SamirBoulil/slub/comments{/number}",
                "issue_comment_url": "https://api.github.com/repos/SamirBoulil/slub/issues/comments{/number}",
                "contents_url": "https://api.github.com/repos/SamirBoulil/slub/contents/{+path}",
                "compare_url": "https://api.github.com/repos/SamirBoulil/slub/compare/{base}...{head}",
                "merges_url": "https://api.github.com/repos/SamirBoulil/slub/merges",
                "archive_url": "https://api.github.com/repos/SamirBoulil/slub/{archive_format}{/ref}",
                "downloads_url": "https://api.github.com/repos/SamirBoulil/slub/downloads",
                "issues_url": "https://api.github.com/repos/SamirBoulil/slub/issues{/number}",
                "pulls_url": "https://api.github.com/repos/SamirBoulil/slub/pulls{/number}",
                "milestones_url": "https://api.github.com/repos/SamirBoulil/slub/milestones{/number}",
                "notifications_url": "https://api.github.com/repos/SamirBoulil/slub/notifications{?since,all,participating}",
                "labels_url": "https://api.github.com/repos/SamirBoulil/slub/labels{/name}",
                "releases_url": "https://api.github.com/repos/SamirBoulil/slub/releases{/id}",
                "deployments_url": "https://api.github.com/repos/SamirBoulil/slub/deployments",
                "created_at": "2019-01-17T20:21:39Z",
                "updated_at": "2019-02-24T11:35:21Z",
                "pushed_at": "2019-02-24T12:33:25Z",
                "git_url": "git://github.com/SamirBoulil/slub.git",
                "ssh_url": "git@github.com:SamirBoulil/slub.git",
                "clone_url": "https://github.com/SamirBoulil/slub.git",
                "svn_url": "https://github.com/SamirBoulil/slub",
                "homepage": "",
                "size": 211,
                "stargazers_count": 0,
                "watchers_count": 0,
                "language": "PHP",
                "has_issues": true,
                "has_projects": true,
                "has_downloads": true,
                "has_wiki": true,
                "has_pages": false,
                "forks_count": 0,
                "mirror_url": null,
                "archived": false,
                "open_issues_count": 1,
                "license": {
                    "key": "mit",
                    "name": "MIT License",
                    "spdx_id": "MIT",
                    "url": "https://api.github.com/licenses/mit",
                    "node_id": "MDc6TGljZW5zZTEz"
                },
                "forks": 0,
                "open_issues": 1,
                "watchers": 0,
                "default_branch": "master"
            }
        },
        "_links": {
            "self": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/pulls/10"
            },
            "html": {
                "href": "https://github.com/SamirBoulil/slub/pull/10"
            },
            "issue": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/issues/10"
            },
            "comments": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/issues/10/comments"
            },
            "review_comments": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/pulls/10/comments"
            },
            "review_comment": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/pulls/comments{/number}"
            },
            "commits": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/pulls/10/commits"
            },
            "statuses": {
                "href": "https://api.github.com/repos/SamirBoulil/slub/statuses/caed81fdd51f573d7235fb63959ef6a6d0aaf809"
            }
        },
        "author_association": "OWNER"
    },
    "repository": {
        "id": 166291213,
        "node_id": "MDEwOlJlcG9zaXRvcnkxNjYyOTEyMTM=",
        "name": "slub",
        "full_name": "SamirBoulil/slub",
        "private": false,
        "owner": {
            "login": "SamirBoulil",
            "id": 1826473,
            "node_id": "MDQ6VXNlcjE4MjY0NzM=",
            "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
            "gravatar_id": "",
            "url": "https://api.github.com/users/SamirBoulil",
            "html_url": "https://github.com/SamirBoulil",
            "followers_url": "https://api.github.com/users/SamirBoulil/followers",
            "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
            "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
            "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
            "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
            "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
            "repos_url": "https://api.github.com/users/SamirBoulil/repos",
            "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
            "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
            "type": "User",
            "site_admin": false
        },
        "html_url": "https://github.com/SamirBoulil/slub",
        "description": "Improve the feedback loop between Github pull requests statuses and teams using Slack.",
        "fork": false,
        "url": "https://api.github.com/repos/SamirBoulil/slub",
        "forks_url": "https://api.github.com/repos/SamirBoulil/slub/forks",
        "keys_url": "https://api.github.com/repos/SamirBoulil/slub/keys{/key_id}",
        "collaborators_url": "https://api.github.com/repos/SamirBoulil/slub/collaborators{/collaborator}",
        "teams_url": "https://api.github.com/repos/SamirBoulil/slub/teams",
        "hooks_url": "https://api.github.com/repos/SamirBoulil/slub/hooks",
        "issue_events_url": "https://api.github.com/repos/SamirBoulil/slub/issues/events{/number}",
        "events_url": "https://api.github.com/repos/SamirBoulil/slub/events",
        "assignees_url": "https://api.github.com/repos/SamirBoulil/slub/assignees{/user}",
        "branches_url": "https://api.github.com/repos/SamirBoulil/slub/branches{/branch}",
        "tags_url": "https://api.github.com/repos/SamirBoulil/slub/tags",
        "blobs_url": "https://api.github.com/repos/SamirBoulil/slub/git/blobs{/sha}",
        "git_tags_url": "https://api.github.com/repos/SamirBoulil/slub/git/tags{/sha}",
        "git_refs_url": "https://api.github.com/repos/SamirBoulil/slub/git/refs{/sha}",
        "trees_url": "https://api.github.com/repos/SamirBoulil/slub/git/trees{/sha}",
        "statuses_url": "https://api.github.com/repos/SamirBoulil/slub/statuses/{sha}",
        "languages_url": "https://api.github.com/repos/SamirBoulil/slub/languages",
        "stargazers_url": "https://api.github.com/repos/SamirBoulil/slub/stargazers",
        "contributors_url": "https://api.github.com/repos/SamirBoulil/slub/contributors",
        "subscribers_url": "https://api.github.com/repos/SamirBoulil/slub/subscribers",
        "subscription_url": "https://api.github.com/repos/SamirBoulil/slub/subscription",
        "commits_url": "https://api.github.com/repos/SamirBoulil/slub/commits{/sha}",
        "git_commits_url": "https://api.github.com/repos/SamirBoulil/slub/git/commits{/sha}",
        "comments_url": "https://api.github.com/repos/SamirBoulil/slub/comments{/number}",
        "issue_comment_url": "https://api.github.com/repos/SamirBoulil/slub/issues/comments{/number}",
        "contents_url": "https://api.github.com/repos/SamirBoulil/slub/contents/{+path}",
        "compare_url": "https://api.github.com/repos/SamirBoulil/slub/compare/{base}...{head}",
        "merges_url": "https://api.github.com/repos/SamirBoulil/slub/merges",
        "archive_url": "https://api.github.com/repos/SamirBoulil/slub/{archive_format}{/ref}",
        "downloads_url": "https://api.github.com/repos/SamirBoulil/slub/downloads",
        "issues_url": "https://api.github.com/repos/SamirBoulil/slub/issues{/number}",
        "pulls_url": "https://api.github.com/repos/SamirBoulil/slub/pulls{/number}",
        "milestones_url": "https://api.github.com/repos/SamirBoulil/slub/milestones{/number}",
        "notifications_url": "https://api.github.com/repos/SamirBoulil/slub/notifications{?since,all,participating}",
        "labels_url": "https://api.github.com/repos/SamirBoulil/slub/labels{/name}",
        "releases_url": "https://api.github.com/repos/SamirBoulil/slub/releases{/id}",
        "deployments_url": "https://api.github.com/repos/SamirBoulil/slub/deployments",
        "created_at": "2019-01-17T20:21:39Z",
        "updated_at": "2019-02-24T11:35:21Z",
        "pushed_at": "2019-02-24T12:33:25Z",
        "git_url": "git://github.com/SamirBoulil/slub.git",
        "ssh_url": "git@github.com:SamirBoulil/slub.git",
        "clone_url": "https://github.com/SamirBoulil/slub.git",
        "svn_url": "https://github.com/SamirBoulil/slub",
        "homepage": "",
        "size": 211,
        "stargazers_count": 0,
        "watchers_count": 0,
        "language": "PHP",
        "has_issues": true,
        "has_projects": true,
        "has_downloads": true,
        "has_wiki": true,
        "has_pages": false,
        "forks_count": 0,
        "mirror_url": null,
        "archived": false,
        "open_issues_count": 1,
        "license": {
            "key": "mit",
            "name": "MIT License",
            "spdx_id": "MIT",
            "url": "https://api.github.com/licenses/mit",
            "node_id": "MDc6TGljZW5zZTEz"
        },
        "forks": 0,
        "open_issues": 1,
        "watchers": 0,
        "default_branch": "master"
    },
    "sender": {
        "login": "SamirBoulil",
        "id": 1826473,
        "node_id": "MDQ6VXNlcjE4MjY0NzM=",
        "avatar_url": "https://avatars1.githubusercontent.com/u/1826473?v=4",
        "gravatar_id": "",
        "url": "https://api.github.com/users/SamirBoulil",
        "html_url": "https://github.com/SamirBoulil",
        "followers_url": "https://api.github.com/users/SamirBoulil/followers",
        "following_url": "https://api.github.com/users/SamirBoulil/following{/other_user}",
        "gists_url": "https://api.github.com/users/SamirBoulil/gists{/gist_id}",
        "starred_url": "https://api.github.com/users/SamirBoulil/starred{/owner}{/repo}",
        "subscriptions_url": "https://api.github.com/users/SamirBoulil/subscriptions",
        "organizations_url": "https://api.github.com/users/SamirBoulil/orgs",
        "repos_url": "https://api.github.com/users/SamirBoulil/repos",
        "events_url": "https://api.github.com/users/SamirBoulil/events{/privacy}",
        "received_events_url": "https://api.github.com/users/SamirBoulil/received_events",
        "type": "User",
        "site_admin": false
    }
}
JSON;

        return $json;
    }
}
