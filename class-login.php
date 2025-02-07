<?php

class Login {
	private $db;

	public function __construct($db) {
        $this->db = $db;
    }

    public function checkUser() {
    	if (isset($_COOKIE['PHPSESSID'])) {
    		$user = $this->db->getSessionUser($_COOKIE['PHPSESSID']);
    		if ($user) {
    			$_SESSION['user_id'] = $user;
    		}
    	}
    	if (!isset($_SESSION['user_id'])) {
    		$this->loginForm();
    	}
    }

    public function login($username, $password) {
    	if ($this->db->checkPassword($username, $password)) {
    		$user = $this->db->getUser($username);
    		$_SESSION['user_id'] = $user['id'];
    		$this->db->setSessionUser($_COOKIE['PHPSESSID'], $user['id']);
       	}
    }

    public function logout() {
    	$this->db->deleteSession($_COOKIE['PHPSESSID']);
		if (ini_get("session.use_cookies")) {
			$params = session_get_cookie_params();
			setcookie(
				session_name(), '', time() - 42000,
				$params["path"], $params["domain"],
				$params["secure"], $params["httponly"]
			);
		}
		unset($_SESSION['user_id']);
    	session_destroy();
    }

    public function handleLoginActions() {
    	if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
			if ($_POST['action'] === 'login') {
				$this->login($_POST['username'], $_POST['password']);
			}
			if ($_POST['action'] === 'logout') {
				$this->logout();
			}
		}
    }

    public function loginForm() {
    	?>
		<form method="post">
			<label>Käyttäjätunnus:</label>
			<input type="text" name="username" autofocus required>
			<label>Salasana:</label>
			<input type="password" name="password" required>
			<input type="hidden" name="action" value="login">
			<button type="submit">Kirjaudu sisään</button>
		</form>
		<?php
		exit();
    }
}
