<?php
interface RootsRatedPosts{

    public function postScheduling($distribution, $rrId, $catName, $postType);

    public function postGoLive($distribution, $launchAt, $rrId, $catName, $postType);

    public function postRevision($distribution, $rrId, $postType, $scheduledAt);

    public function postUpdate($distribution, $rrId, $postType, $scheduledAt);

    public function postRevoke($rrId, $postType);

    public function getInfo();



}