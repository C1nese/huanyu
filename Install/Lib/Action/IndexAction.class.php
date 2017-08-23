<?php
class IndexAction extends Action {
    public function index()
	{
        header("Location:/Install/index.php?s=/Install/index");
    }
}
?>