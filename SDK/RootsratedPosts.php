<?php
interface RootsRatedPosts{

    public function postScheduling($distribution, $rrId, $catName, $postType);

    public function postGoLive($distribution, $launchAt, $rrId, $catName, $postType);

    public function postRevision($postId, $distribution, $scheduledAt);

    public function postUpdate($postId, $scheduledAt);

    public function postRevoke($rrId, $postType);

    public function getInfo();



}