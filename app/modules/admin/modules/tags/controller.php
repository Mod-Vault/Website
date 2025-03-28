<?php
namespace modules\admin\modules\tags;
class controller extends \AdminController {
	function get($f3) {

        $tag = new \Tag();
        $f3->set('tags', $tag->GetTags());

		echo $this->render('index');
	}
	function edit($f3, $params) {

		$tag = !empty($params['resource_id']) ? new \Tag($params['resource_id'], true) : new \Tag();

		if($f3->VERB == "POST") {
			if(empty($tag->Data)) {
				if(($error = $tag->Create($_POST)) !== true) {
                    $f3->set('site_error', $error);
                }
			} else {
				$tag->Update($_POST);
			}

			if(empty($f3->site_error))
				$f3->reroute('admin/tags/edit/' . $tag->uid);
		}

		$f3->set('tag', $tag);

		echo $this->render('edit');
	}
    function get_tags($f3) {
        echo json_encode((array)(new \Tag())->GetTags());
    }
}
