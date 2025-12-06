<?php
require_once __DIR__ . '/config/database.php';

if (session_status() === PHP_SESSION_NONE) session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username && $password) {
        $sql = "SELECT * FROM users WHERE username = ? LIMIT 1";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            header('Location: index.php');
            exit;
        } else {
            $error = 'Usuário ou senha inválidos.';
        }
    } else {
        $error = 'Preencha usuário e senha.';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Contas Domésticas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Pequenas customizações locais para o login */
        .login-hero {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, #0f172a 0%, #0ea5a6 60%);
            color: #fff;
        }
        .login-card {
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(2,6,23,0.5);
            overflow: hidden;
        }
        .login-left {
            background: linear-gradient(180deg, rgba(255,255,255,0.06), rgba(255,255,255,0.03));
            padding: 2.5rem 2rem;
        }
        .login-right {
            padding: 2.5rem 2rem;
            background: #fff;
            color: #333;
        }
        .brand-logo {
            font-size: 1.3rem;
            font-weight: 700;
            letter-spacing: .3px;
            color: #fff;
        }
        .input-icon { width: 42px; }
        @media (max-width: 767px) {
            .login-left { display: none; }
            .login-card { border-radius: 8px; }
        }
    </style>
</head>
<body>
    <div class="login-hero">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card login-card">
                        <div class="row g-0">
                            <div class="col-md-5 login-left d-flex flex-column justify-content-center">
                                <div class="px-3">
                                    <div class="brand-logo mb-3"><i class="fas fa-wallet me-2"></i> Contas Domésticas</div>
                                    <h3 class="fw-bold">Organize seu dinheiro</h3>
                                    <p class="text-white-50">Registre receitas e despesas, controle contas e acompanhe relatórios de forma simples.</p>
                                </div>
                            </div>
                            <div class="col-md-7 login-right">
                                <div class="d-flex justify-content-between align-items-center mb-3">
                                    <h4 class="mb-0">Entrar</h4>
                                    <small class="text-muted">Ainda não tem conta? <a href="register.php">Criar</a></small>
                                </div>

                                <?php if ($error): ?>
                                    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
                                <?php endif; ?>

                                <form method="POST">
                                    <div class="mb-3 input-group">
                                        <span class="input-group-text input-icon bg-light"><i class="fas fa-user"></i></span>
                                        <input type="text" name="username" class="form-control" placeholder="Usuário" required>
                                    </div>

                                    <div class="mb-3 input-group">
                                        <span class="input-group-text input-icon bg-light"><i class="fas fa-lock"></i></span>
                                        <input type="password" id="password" name="password" class="form-control" placeholder="Senha" required>
                                        <button type="button" class="btn btn-light" id="togglePass" title="Mostrar senha"><i class="fa-regular fa-eye"></i></button>
                                    </div>

                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <div>
                                            <input type="checkbox" id="remember" name="remember">
                                            <label for="remember" class="small">Lembrar-me</label>
                                        </div>
                                        <a href="#" class="small">Esqueci a senha</a>
                                    </div>

                                    <div class="d-grid mb-3">
                                        <button class="btn btn-primary btn-lg">Entrar</button>
                                    </div>

                                    <div class="text-center">
                                        <small class="text-muted">ou entrar com</small>
                                    </div>
                                    <div class="d-flex gap-2 justify-content-center mt-3">
                                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-google"></i> Google</button>
                                        <button type="button" class="btn btn-outline-secondary"><i class="fab fa-github"></i> GitHub</button>
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
        document.getElementById('togglePass').addEventListener('click', function(){
            const pwd = document.getElementById('password');
            if (pwd.type === 'password') { pwd.type = 'text'; this.innerHTML = '<i class="fa-regular fa-eye-slash"></i>'; }
            else { pwd.type = 'password'; this.innerHTML = '<i class="fa-regular fa-eye"></i>'; }
        });
    </script>
</body>
</html>

<script>
// Validação simples client-side para login
(() => {
    const form = document.querySelector('form');
    if (!form) return;
    const username = form.querySelector('input[name="username"]');
    const password = form.querySelector('input[name="password"]');

    function showErr(el,msg){ el.classList.add('is-invalid'); let p = el.parentNode; let fb = p.querySelector('.invalid-feedback'); if(!fb){ fb = document.createElement('div'); fb.className='invalid-feedback'; p.appendChild(fb);} fb.textContent=msg; }
    function clearErr(el){ el.classList.remove('is-invalid'); let p=el.parentNode; let fb=p.querySelector('.invalid-feedback'); if(fb) fb.textContent=''; }

    [username,password].forEach(i=>i && i.addEventListener('input', ()=> clearErr(i)));

    form.addEventListener('submit', function(e){
        let ok = true;
        if (!username.value.trim()){ showErr(username,'Informe o usuário'); ok=false; }
        if (!password.value.trim() || password.value.length < 6){ showErr(password,'Senha inválida (mín 6 caracteres)'); ok=false; }
        if (!ok) { e.preventDefault(); }
    });
})();
</script>
