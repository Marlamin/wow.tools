<?php
include("inc/config.php");
die("User functionality disabled");
function passwordMeetsReqs($password)
{
    return (!empty($password) || trim($password) !== $password || strlen($password) < 12);
}

if (empty($_GET['p'])) {
    header("Location: index.php");
    die();
}

if ($_GET['p'] == "login") {
    if (!empty($_POST)) {
        if (
            empty(trim($_POST['username'])) ||
            !passwordMeetsReqs($_POST['password'])
        ) {
            $message['type'] = "danger";
            $message['text'] = "Not all required fields were filled in or requirements were not met.";
        } else {
            $q = $pdo->prepare('SELECT * FROM users WHERE username = :username');
            $q->bindParam(':username', $_POST['username']);
            $q->execute();
            $r = $q->fetch();

            if (!empty($r)) {
                if (password_verify($_POST['password'], $r['password'])) {
                    $message['type'] = "success";
                    $message['text'] = "You were succesfully logged in.";

                    session_start();
                    $_SESSION['loggedin'] = true;
                    $_SESSION['userid'] = $r['id'];
                    $_SESSION['user'] = $r['username'];
                    $_SESSION['rank'] = $r['rank'];
                    session_write_close();

                    header("Location: index.php");
                } else {
                    $message['type'] = "danger";
                    $message['text'] = "<b>Invalid credentials.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>..";
                }
            } else {
                $message['type'] = "danger";
                $message['text'] = "<b>Invalid credentials.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>..";
            }
        }
    }
} else if ($_GET['p'] == "register") {
    if (!empty($_POST)) {
        if (
            empty(trim($_POST['username'])) ||
            strip_tags($_POST['username']) !== $_POST['username'] ||
            empty(trim($_POST['email'])) ||
            !filter_var(trim($_POST['email']), FILTER_VALIDATE_EMAIL) ||
            !passwordMeetsReqs($_POST['password'])
        ) {
            $message['type'] = "danger";
            $message['text'] = "Not all required fields were filled in or requirements were not met.";
        } else {
            $q = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");
            $q->bindParam(":username", $_POST['username']);
            $q->bindParam(":email", $_POST['email']);
            $q->bindValue(":password", password_hash($_POST['password'], PASSWORD_DEFAULT));
            try {
                $q->execute();
            } catch (Exception $e) {
                $message['type'] = "danger";
                $message['text'] = "<b>Username or e-mail already exists or something else went wrong.</b><br>Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>.";
            }

            if (empty($message)) {
                $message['type'] = "success";
                $message['text'] = "Your account '" . htmlentities($_POST['username']) . "' has been created. You can now <a href='/user.php?p=login'>login</a>.";
            }
        }
    }
} else if ($_GET['p'] == "recover") {
    if (!empty($_POST)) {
        if (!empty(trim($_POST['email'])) && filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            $q = $pdo->prepare("SELECT username, email FROM users WHERE email = ?");
            $q->execute([$_POST['email']]);
            $res = $q->fetch();
            if (empty($res)) {
                $message['type'] = "success";
                $message['text'] = "If this e-mail address is valid, a message containing a recovery link has been sent to it.";
            } else {
                $recoverytoken = bin2hex(random_bytes(16));

                $q = $pdo->prepare("UPDATE users SET recoverytoken = ?, tokengeneratedon = NOW() WHERE email = ?");
                $q->execute([$recoverytoken, $res['email']]);

                $mail = "Hey " . $res['username'] . ", please click the following link to reset your password: https://wow.tools/user.php?p=resetpass&token=" . $recoverytoken . " \nThis link will be valid for 1 day.";

                sendgridMail($res['email'], "WoW.tools recovery link", $mail);

                $message['type'] = "success";
                $message['text'] = "If this e-mail address is valid, a message containing a recovery link has been sent to it.";
            }
        } else {
            $message['type'] = "danger";
            $message['text'] = "Invalid e-mail address";
        }
    }
} else if ($_GET['p'] == "resetpass") {
    $tokenvalid = false;
    // Check if user can change password or not with this token
    if (!empty($_GET['token'])) {
        if (strlen($_GET['token']) == 32 && ctype_xdigit($_GET['token'])) {
            $q = $pdo->prepare("SELECT tokengeneratedon FROM users WHERE recoverytoken = ?");
            $q->execute([$_GET['token']]);
            $res = $q->fetch();
            if (empty($res)) {
                $message['type'] = "danger";
                $message['text'] = "Invalid token.";
            } else {
                $tokengeneratedon = strtotime($res['tokengeneratedon']);
                if ($tokengeneratedon < strtotime("-1 day")) {
                    // Token expired
                    $q = $pdo->prepare("UPDATE users SET recoverytoken = NULL, tokengeneratedon = NULL WHERE recoverytoken = ?");
                    $q->execute([$_GET['token']]);

                    $message['type'] = "danger";
                    $message['text'] = "Your token has expired, please request a new password change.";
                } else {
                    // Token valid
                    $tokenvalid = true;
                }
            }
        } else {
            $message['type'] = "danger";
            $message['text'] = "Invalid token.";
        }
    } else {
        $message['type'] = "danger";
        $message['text'] = "No token given.";
    }

    // Reset pass if all fields are filled in and valid
    if (!empty($_POST)) {
        if (
            !empty($_POST['password']) &&
            !empty($_POST['password2']) &&
            passwordMeetsReqs($_POST['password']) &&
            $tokenvalid
        ) {
            if ($_POST['password'] === $_POST['password2']) {
                $q = $pdo->prepare("UPDATE users SET password = :password, recoverytoken = NULL, tokengeneratedon = NULL WHERE recoverytoken = :token LIMIT 1");
                $q->bindValue(":password", password_hash($_POST['password'], PASSWORD_DEFAULT));
                $q->bindParam(":token", $_GET['token']);
                $q->execute();
                $message['type'] = "success";
                $message['text'] = "Password changed. You can now login with your new details <a href='/user.php?p=login'>here</a>.";
            } else {
                $message['type'] = "danger";
                $message['text'] = "Passwords do not match or do not meet requirements";
            }
        } else {
            $message['type'] = "danger";
            $message['text'] = "Invalid input.";
        }
    }
} else if ($_GET['p'] == 'logout') {
    header("Location: index.php");
    session_start();
    session_destroy();
    session_write_close();
    die();
}

include("inc/header.php");

if (!isset($_SESSION['loggedin'])) {
    if ($_GET['p'] == "register") {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 offset-md-4">
                    <br>
                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
                    <?php } ?>
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
        <?php
    } else if ($_GET['p'] == "resetpass") {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 offset-md-4 mx-auto">
                    <br>
                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
                    <?php } ?>
                    <?php if ($tokenvalid) { ?>
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
                    <?php } ?>
                </div>
            </div>
        </div>
        <?php
    } else if ($_GET['p'] == "recover") {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 offset-md-4 mx-auto">
                    <br>
                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
                    <?php } ?>
                    <form method="POST" action="user.php?p=recover">
                        <div class="form-group">
                            <label for="email">Enter the e-mail address connected to your account</label>
                            <input type="text" id="email" minlength="12" maxlength="72" class="form-control" placeholder="E-mail" name="email">
                            <small class="form-text">Don't remember? Mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    } else if ($_GET['p'] == "login") {
        ?>
        <div class="container-fluid">
            <div class="row">
                <div class="col-md-4 offset-md-4 mx-auto">
                    <br>
                    <?php if (!empty($message)) { ?>
                        <div class="alert alert-<?=$message['type']?>"><?=$message['text']?></div>
                    <?php } ?>
                    <form method="POST" action="user.php?p=login">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" id="username" class="form-control" placeholder="Username" name="username" tabindex='1'>
                            <small class="form-text">No account yet? <a href='/user.php?p=register'>Register here</a>.</small>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" id="password" minlength="12" maxlength="72" class="form-control" placeholder="Password" name="password" tabindex='2'>
                            <small class="form-text">Forgot your password? Try <a href='/user.php?p=recover'>recovering your account</a>. <br>If that doesn't work, mail me at <a href='mailto:marlamin@marlamin.com'>marlamin@marlamin.com</a>.</small>
                        </div>
                        <button type="submit" class="btn btn-primary">Submit</button>
                    </form>
                </div>
            </div>
        </div>
        <?php
    }
}
