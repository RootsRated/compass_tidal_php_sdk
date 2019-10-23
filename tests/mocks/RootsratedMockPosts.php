<?php
require_once(__DIR__ . '/../../SDK/RootsratedPosts.php');

class RootsRatedMockPosts implements RootsRatedPosts
{

    // Public Functions
    public function postScheduling($distribution, $rrId, $postType)
    {

    }

    public function postGoLive($distribution, $launchAt, $rrId, $postType)
    {

    }

    public function postRevision($distribution, $rrId, $postType, $scheduledAt)
    {

    }

    public function postUpdate($distribution, $rrId, $postType, $scheduledAt)
    {

    }

    public function postRevoke($rrId, $postType)
    {

    }

    public function deactivationPlugin()
    {

    }

    public function getInfo()
    {
        $info = array();

        $info['db_version'] = '3.1.1';
        $info['siteurl'] = '';
        $info['home'] = '';
        $info['publish_posts'] =  true;
        $info['delete_published_posts'] =  true;
        $info['username_exists'] = true;
        $info['plugins'] = array();
        $info['plugins_url'] = 'wp-content/plugins/';
	$info['upload_path'] = 'wp-content/uploads/';

        return $info;
    }

}
