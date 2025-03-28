<?php
class AdminController extends Controller {
	function __construct($f3, $params) {
		parent::__construct($f3, $params);
		$f3 = $this->f3();

		if($f3->active_user->uid == null || !$f3->active_user->IsAdmin) {
			die('You do not have permission to do that.');
		}
	}
	public function render($content, $template = 'default') {
		if($template == "default") {
			$template = $this->f3()->get('defaultTemplate');
		}
		$module = $this->f3()->module;
		$this->f3()->set('content', "admin/modules/$module/views/$content.htm");
		return \Template::instance()->render("../$template.htm");
	}

	public function model($model, $module_override = null) {
		$module = $module_override === null ? $this->f3()->module : $module_override;
		$class = "\\modules\\admin\\modules\\{$module}\\models\\{$model}";
		return new $class();
	}
}
