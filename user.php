<?
include("inc/config.php");

function passwordMeetsReqs($password){
	return (!empty($password) || trim($password) !== $password || strlen($password) < 12);
}

if($_GET['p'] == "login"){
	if(!empty($_POST)){
		if(
			empty(trim($_POST['username'])) ||
			!passwordMeetsReqs($_POST['password'])
		){
			$message['type'] = "danger";
			$message['text'] = "Not all required fields were filled in or requirements were not met.";
		}else{
			$q = $pdo->prepare('SELECT * FROM users WHERE username = :username');
			$q->bindParam(':username', $_POST['username']);
			$q->execute();
			$r = $q->fetch();

			if(!empty($r)){
				if(password_verify($_POST['password'], $r['password'])){
					$message['type'] = "success";
					$message['text'] = "You were succesfully logged in.";

					$_SESSION['loggedin'] = true;
					$_SESSION['userid'] = $r['id'];
					$_SESSION['user'] = $r['username'];

					header("Location: index.php");
				}else{
					$message['type'] = "danger";
					$message['text'] = "<b>Invalid credentials.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, poke me on IRC, Discord or mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>..";
				}
			}else{
				$message['type'] = "danger";
				$message['text'] = "<b>Invalid credentials.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, poke me on IRC, Discord or mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>..";
			}
		}
	}
}else if($_GET['p'] == "register"){
	if(!empty($_POST)){
		if(
			empty(trim($_POST['username'])) ||
			strip_tags($_POST['username']) !== $_POST['username'] ||
			empty(trim($_POST['email'])) ||
			!filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) ||
			!passwordMeetsReqs($_POST['password'])
		){
			$message['type'] = "danger";
			$message['text'] = "Not all required fields were filled in or requirements were not met.";
		}else{
			$q = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
			$q->bindParam(":username", $_POST['username']);
			$q->bindParam(":email", $_POST['email']);
			$q->bindValue(":password", password_hash($_POST['password'], PASSWORD_DEFAULT));
			try{
				$q->execute();
			}catch(Exception $e){
				$message['type'] = "danger";
				$message['text'] = "<b>Username or e-mail already exists or something else went wrong.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, poke me on IRC, Discord or mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>.";
			}

			if(empty($message)){
				$message['type'] = "success";
				$message['text'] = "Your account '".htmlentities($_POST['username'])."' has been created. You can now <a href='/user.php?p=login'>login</a>.";
			}
		}
	}
}else if($_GET['p'] == "recover"){
	if(!empty($_POST)){
		if(!empty(trim($_POST['email'])) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)){
			$q = $pdo->prepare("SELECT username, email FROM users WHERE email = ?");
			$q->execute([$_POST['email']]);
			$res = $q->fetch();
			if(empty($res)){
				$message['type'] = "danger";
				$message['text'] = "<b>Unknown e-mail address!</b><br>If you are one of the 30 users that signed up before e-mails were required during registering, give me a poke on Discord or IRC to resolve this.";
			}else{
				$recoverytoken = bin2hex(random_bytes(16));

				$q = $pdo->prepare("UPDATE users SET recoverytoken = ?, tokengeneratedon = NOW() WHERE email = ?");
				$q->execute([$recoverytoken, $res['email']]);

				$mail = "Dear ".$res['username'].", please click the following link to reset your password: https://wow.tools/user.php?p=resetpass&token=" . $recoverytoken .". \nThis link will be valid for 1 day.";

				sendgridMail($res['email'], "WoW.tools recovery link", $mail);

				$message['type'] = "success";
				$message['text'] = "A message containing a recovery link has been sent to the specified e-mail address.";
			}
		}else{
			$message['type'] = "danger";
			$message['text'] = "Invalid e-mail address";
		}
	}
}else if($_GET['p'] == "resetpass"){
	$tokenvalid = false;
	// Check if user can change password or not with this token
	if(!empty($_GET['token'])){
		if(strlen($_GET['token']) == 32 && ctype_xdigit($_GET['token'])){
			$q = $pdo->prepare("SELECT tokengeneratedon FROM users WHERE recoverytoken = ?");
			$q->execute([$_GET['token']]);
			$res = $q->fetch();
			if(empty($res)){
				$message['type'] = "danger";
				$message['text'] = "Invalid token.";
			}else{
				$tokengeneratedon = strtotime($res['tokengeneratedon']);
				if($tokengeneratedon < strtotime("-1 day")){
					// Token expired
					$q = $pdo->prepare("UPDATE users SET recoverytoken = NULL, tokengeneratedon = NULL WHERE recoverytoken = ?");
					$q->execute([$_GET['token']]);

					$message['type'] = "danger";
					$message['text'] = "Your token has expired, please request a new password change.";
				}else{
					// Token valid
					$tokenvalid = true;
				}
			}
		}else{
			$message['type'] = "danger";
			$message['text'] = "Invalid token.";
		}
	}else{
		$message['type'] = "danger";
		$message['text'] = "No token given.";
	}

	// Reset pass if all fields are filled in and valid
	if(!empty($_POST)){
		if(
			!empty($_POST['password']) &&
			!empty($_POST['password2']) &&
			passwordMeetsReqs($_POST['password']) &&
			$tokenvalid){
			if($_POST['password'] === $_POST['password2']){
				$q = $pdo->prepare("UPDATE users SET password = :password, recoverytoken = NULL, tokengeneratedon = NULL WHERE recoverytoken = :token LIMIT 1");
				$q->bindValue(":password", password_hash($_POST['password'], PASSWORD_DEFAULT));
				$q->bindParam(":token", $_GET['token']);
				$q->execute();
				$message['type'] = "success";
				$message['text'] = "Password changed. You can now login with your new details <a href='/user.php?p=login'>here</a>.";
			}else{
				$message['type'] = "danger";
				$message['text'] = "Passwords do not match or do not meet requirements";
			}
		}else{
			$message['type'] = "danger";
			$message['text'] = "Invalid input.";
		}
	}
}else if($_GET['p'] == 'logout'){
	header("Location: index.php");
	session_destroy();
	die();
}

include("inc/header.php");

if(empty($_GET['p'])){
	header("Location: index.php");
	die();
}

if(!isset($_SESSION['loggedin'])){
	if($_GET['p'] == "register"){
		?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 offset-md-4">
					<br>
					<? if(!empty($message)){ ?>
						<div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
					<? } ?>
					<p>
					</p>
				</div>
				<div class="col-md-4 offset-md-4">
					<form method="POST" action="user.php?p=register">
						<div class="form-group">
							<label for="email">E-mail</label>
							<input type="email" id="email" class="form-control" placeholder="Enter e-mail" name="email" REQUIRED>
							<small class="form-text">Note: This is used for account recovery.</small>
						</div>
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" id="username" class="form-control" placeholder="Enter username" name="username" REQUIRED>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" id="password" minlength="12" maxlength="72" class="form-control" placeholder="Password" name="password" REQUIRED>
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
					</form>
				</div>
			</div>
		</div>
		<?
	}else if($_GET['p'] == "resetpass"){
		?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 offset-md-4 mx-auto">
					<br>
					<? if(!empty($message)){ ?>
						<div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
					<? } ?>
					<? if($tokenvalid){ ?>
						<form method="POST" action="user.php?p=resetpass&token=<?=$_GET['token']?>">
							<div class="form-group">
								<label for="password">New password</label>
								<input type="password" id="password" minlength="12" maxlength="72" class="form-control" placeholder="Password" name="password">
							</div>
							<div class="form-group">
								<label for="password2">New password (again)</label>
								<input type="password" id="password2" minlength="12" maxlength="72" class="form-control" placeholder="Password (again)" name="password2">
							</div>
							<button type="submit" class="btn btn-primary">Submit</button>
						</form>
					<? } ?>
				</div>
			</div>
		</div>
		<?
	}else if($_GET['p'] == "recover"){
		?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 offset-md-4 mx-auto">
					<br>
					<? if(!empty($message)){ ?>
						<div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
					<? } ?>
					<form method="POST" action="user.php?p=recover">
						<div class="form-group">
							<label for="email">Enter the e-mail address connected to your account</label>
							<input type="text" id="email" minlength="12" maxlength="72" class="form-control" placeholder="E-mail" name="email">
							<small class="form-text">Don't remember? Contact me on Discord.</small>
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
					</form>
				</div>
			</div>
		</div>
		<?
	}else if($_GET['p'] == "login"){
	// Log in
		if(empty($message)){
			$message['type'] = 'success';
			$message['text'] = 'If you already had an account on the old site, it was transferred over to this one.';
		}
		?>
		<div class="container-fluid">
			<div class="row">
				<div class="col-md-4 offset-md-4 mx-auto">
					<br>
					<? if(!empty($message)){ ?>
						<div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
					<? } ?>
					<form method="POST" action="user.php?p=login">
						<div class="form-group">
							<label for="username">Username</label>
							<input type="text" id="username" class="form-control" placeholder="Username" name="username" tabindex='1'>
							<small class="form-text">No account yet? <a href='/user.php?p=register'>Register here</a>.</small>
						</div>
						<div class="form-group">
							<label for="password">Password</label>
							<input type="password" id="password" minlength="12" maxlength="72" class="form-control" placeholder="Password" name="password" tabindex='2'>
							<small class="form-text">Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, poke me on IRC, Discord or mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>.</small>
						</div>
						<button type="submit" class="btn btn-primary">Submit</button>
					</form>
				</div>
			</div>
		</div>
		<?
	}
}

include("inc/footer.php");
?>