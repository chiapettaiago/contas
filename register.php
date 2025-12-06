<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';
$success = '';

// Gerar CAPTCHA simples (soma) se não existir
if (empty($_SESSION['reg_captcha_a']) || empty($_SESSION['reg_captcha_b'])) {
    $_SESSION['reg_captcha_a'] = random_int(2, 9);
    $_SESSION['reg_captcha_b'] = random_int(1, 9);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';
    $captcha = trim($_POST['captcha'] ?? '');
    $honeypot = trim($_POST['website'] ?? ''); // campo escondido para bots

    // validações básicas
    if ($honeypot !== '') {
        $error = 'Detecção de bot.';
    } elseif (!$fullname || !$email || !$username || !$password) {
        $error = 'Preencha todos os campos obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'E-mail inválido.';
    } elseif ($password !== $password2) {
        $error = 'Senhas não conferem.';
    } elseif (!ctype_digit($captcha) || (int)$captcha !== ($_SESSION['reg_captcha_a'] + $_SESSION['reg_captcha_b'])) {
        $error = 'Verificação anti-robô incorreta.';
        // renovar captcha
        $_SESSION['reg_captcha_a'] = random_int(2, 9);
        $_SESSION['reg_captcha_b'] = random_int(1, 9);
    } else {
        // verificar existência por username ou email
        try {
            $sql = "SELECT id FROM users WHERE username = ? OR email = ? LIMIT 1";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$username, $email]);
            if ($stmt->fetch()) {
                $error = 'Usuário ou e-mail já existe.';
            } else {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $sql = "INSERT INTO users (fullname, email, username, password) VALUES (?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                if ($stmt->execute([$fullname, $email, $username, $hash])) {
                    $success = 'Conta criada com sucesso. Faça login.';
                    // limpar captcha para próxima vez
                    unset($_SESSION['reg_captcha_a'], $_SESSION['reg_captcha_b']);
                } else {
                    $error = 'Erro ao criar conta.';
                }
            }
        } catch (PDOException $e) {
            // Mensagem amigável para quando a tabela users não existir ou houver problema de banco
            $error = 'Erro no banco de dados: ' . $e->getMessage() . '. Verifique se a tabela `users` existe e execute a migração: php setup/add_user_support.php';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registrar - Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        .register-hero { min-height: 100vh; display:flex; align-items:center; background: linear-gradient(135deg, #0f172a 0%, #0ea5a6 60%); color:#fff; }
        .register-card { border-radius:12px; box-shadow:0 10px 30px rgba(2,6,23,0.5); overflow:hidden; }
        .register-left { background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03)); padding:2.5rem 2rem; }
        .register-right { padding:2.5rem 2rem; background:#fff; color:#333; }
        @media (max-width:767px) { .register-left{display:none;} .register-card{border-radius:8px;} }
    </style>
</head>
<body>
    <div class="register-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card register-card">
                        <div class="row g-0">
                            <div class="col-md-5 register-left d-flex flex-column justify-content-center">
                                <div class="px-3">
                                    <div class="brand-logo mb-3"><i class="fas fa-wallet me-2"></i> Contas Domésticas</div>
                                    <h3 class="fw-bold">Comece a controlar suas finanças</h3>
                                    <p class="text-white-50">Crie sua conta e registre receitas, despesas e contas de forma fácil e segura.</p>
                                </div>
                            </div>
                            <div class="col-md-7 register-right">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Criar Conta</h4>
                                    <small class="text-muted">Já tem conta? <a href="login.php">Entrar</a></small>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>
                                <?php if ($success): ?>
                                    <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Nome completo</label>
                                        <input type="text" name="fullname" class="form-control" required value="<?= isset($fullname) ? htmlspecialchars($fullname) : '' ?>">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">E-mail</label>
                                        <input type="email" name="email" class="form-control" required value="<?= isset($email) ? htmlspecialchars($email) : '' ?>">
                                    </div>

                                    <div class="mb-3 input-group">
                                        <span class="input-group-text input-icon bg-light"><i class="fas fa-user"></i></span>
                                        <input type="text" name="username" class="form-control" placeholder="Usuário" required value="<?= isset($username) ? htmlspecialchars($username) : '' ?>">
                                    </div>

                                    <div class="mb-3 input-group">
                                        <span class="input-group-text input-icon bg-light"><i class="fas fa-lock"></i></span>
                                        <input type="password" id="password1" name="password" class="form-control" placeholder="Senha" required>
                                        <button type="button" class="btn btn-light" id="togglePass1" title="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
                                    </div>

                                    <div class="mb-3 input-group">
                                        <span class="input-group-text input-icon bg-light"><i class="fas fa-lock"></i></span>
                                        <input type="password" id="password2" name="password2" class="form-control" placeholder="Repetir Senha" required>
                                        <button type="button" class="btn btn-light" id="togglePass2" title="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
                                    </div>

                                    <div class="mb-3" style="display:none;">
                                        <!-- Honeypot para bots -->
                                        <label>Website</label>
                                        <input type="text" name="website" class="form-control">
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label">Verificação: quanto é <?= $_SESSION['reg_captcha_a'] ?> + <?= $_SESSION['reg_captcha_b'] ?> ?</label>
                                        <input type="text" name="captcha" class="form-control" required>
                                    </div>

                                    <div class="d-grid mb-2">
                                        <button class="btn btn-primary btn-lg">Criar Conta</button>
                                    </div>

                                    <div class="text-center text-muted small">
                                        Ao criar a conta você aceita os termos de uso.
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('togglePass1').addEventListener('click', function(){
            const pwd = document.getElementById('password1');
            if (pwd.type === 'password') { pwd.type = 'text'; this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
            else { pwd.type = 'password'; this.innerHTML = '<i class="fa-regular fa-eye"></i>'; }
        });
        document.getElementById('togglePass2').addEventListener('click', function(){
            const pwd = document.getElementById('password2');
            if (pwd.type === 'password') { pwd.type = 'text'; this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
            else { pwd.type = 'password'; this.innerHTML = '<i class="fa-regular fa-eye"></i>'; }
        });
    </script>
</body>
</html>

<script>
// Validação client-side para register.php
(() => {
    const form = document.querySelector('form');
    if (!form) return;

    const fullname = form.querySelector('input[name="fullname"]');
    const email = form.querySelector('input[name="email"]');
    const username = form.querySelector('input[name="username"]');
    const pw1 = document.getElementById('password1');
    const pw2 = document.getElementById('password2');
    const captcha = form.querySelector('input[name="captcha"]');

    function showError(el, msg) {
        const parent = el.closest('.input-group') || el.parentNode;
        el.classList.add('is-invalid');
        let fb = parent.querySelector('.invalid-feedback');
        if (!fb) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            parent.appendChild(fb);
        }
        fb.textContent = msg;
    }

    function clearError(el) {
        el.classList.remove('is-invalid');
        const parent = el.closest('.input-group') || el.parentNode;
        const fb = parent.querySelector('.invalid-feedback');
        if (fb) fb.textContent = '';
    }

    function validEmail(v) {
        return /^[^@\s]+@[^@\s]+\.[^@\s]+$/.test(v);
    }

    function strongPassword(v) {
        return v.length >= 8 && /[0-9]/.test(v);
    }

    const inputs = [fullname, email, username, pw1, pw2, captcha];
    inputs.forEach(i => { if (i) i.addEventListener('input', () => clearError(i)); });

    form.addEventListener('submit', function(e){
        let ok = true;
        if (!fullname.value.trim()) { showError(fullname, 'Nome completo é obrigatório'); ok = false; }
        if (!email.value.trim() || !validEmail(email.value.trim())) { showError(email, 'E-mail inválido'); ok = false; }
        if (!username.value.trim() || username.value.trim().length < 3) { showError(username, 'Usuário deve ter ao menos 3 caracteres'); ok = false; }
        if (!strongPassword(pw1.value)) { showError(pw1, 'Senha fraca — mínimo 8 caracteres e ao menos um dígito'); ok = false; }
        if (pw1.value !== pw2.value) { showError(pw2, 'Senhas não conferem'); ok = false; }
        if (!/^[0-9]+$/.test(captcha.value.trim())) { showError(captcha, 'Resposta do CAPTCHA deve ser numérica'); ok = false; }

        if (!ok) { e.preventDefault(); window.scrollTo({top: document.querySelector('.register-right').offsetTop - 20, behavior: 'smooth'}); }
    });
})();
</script>
