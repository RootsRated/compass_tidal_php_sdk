<?php
require_once(__DIR__ . '/../../SDK/RootsratedPosts.php');

class RootsRatedMockPosts implements RootsRatedPosts
{

    // Public Functions
    public function postScheduling($distribution, $rrId)
    {

    }

    public function postGoLive($distribution, $launchAt, $rrId)
    {

    }

    public function postRevision($postId, $distribution, $scheduledAt)
    {

    }

    public function postUpdate($postId, $scheduledAt)
    {

    }

    public function postRevoke($rrId)
    {

    }
}