<?php
class Home {
	function Home() {
		
	}
	
	function hasContent($task) {
		$hascontent=false;
		switch ($task) {
			case 'display':
				$hascontent = true;
				break;
		}
		return $hascontent;
	}

	function getTitle($task) {
		$title='';
		switch ($task) {
			case 'display':
				$title='Home'; break;
		}		
		return $title;
	}
	
	function display() {
		
		include 'mods/home/default.php';

	}
	
	function getSubMenu() {
		
	}
	
}