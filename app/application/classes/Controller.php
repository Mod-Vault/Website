<?php



class Controller {

	function __construct($f3, $params) {
		$f3->module = array_key_exists('module', $params) ? $params['module'] : $f3->defaultModule;
		$f3->method = array_key_exists('method', $params) ? $params['method'] : $f3->VERB;
	}

	public function render($content, $template = 'default') {
		if($template == "default") {
			$template = $this->f3()->get('defaultTemplate');
		}
		$module = $this->f3()->module;
		$this->f3()->set('content', "$module/views/$content.htm");
		return \Template::instance()->render("../$template.htm");
	}

	public function model($model, $module_override = null) {
		$module = $module_override === null ? $this->f3()->module : $module_override;
		$class = "\\modules\\{$module}\\models\\{$model}";
		return new $class();
	}

	public function requires_account() {
		if($this->f3()->active_user->uid == null) {
			$this->f3()->reroute('/');
		}
	}

	protected static function f3() {
        return Base::instance();
    }
}
