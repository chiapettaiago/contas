# 🚀 Guia de Início Rápido - Sistema de Contas Domésticas

## ✅ Pré-requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache, Nginx ou built-in do PHP)

## 📦 Passo 1: Criar o Banco de Dados

Abra seu cliente MySQL (phpMyAdmin, MySQL Workbench ou terminal) e execute:

```sql
CREATE DATABASE contas_domesticas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

## ⚙️ Passo 2: Configurar Credenciais

Edite o arquivo `config/database.php`:

```php
define('DB_HOST', 'localhost');    // Host do MySQL
define('DB_USER', 'root');         // Usuário MySQL
define('DB_PASS', '');             // Senha MySQL
define('DB_NAME', 'contas_domesticas');
```

**Exemplo com senha:**
```php
define('DB_PASS', 'minha_senha_super_segura');
```

## 🗄️ Passo 3: Criar as Tabelas

Execute no terminal:

```bash
cd /srv/compartilhada/Iago/projects/contas
php setup/create_tables.php
```

Você deverá ver:
```
✓ Tabelas criadas com sucesso!
```

## 📝 Passo 4 (Opcional): Inserir Dados de Exemplo

```bash
php setup/seed_data.php
```

Isso irá:
- Criar 12 categorias de receita e despesa
- Criar 3 contas de exemplo
- Adicionar 8 transações de exemplo

## 🌐 Passo 5: Iniciar o Servidor

### Opção A: Usar o servidor built-in do PHP

```bash
php -S localhost:8000
```

Acesse: `http://localhost:8000`

### Opção B: Usar Apache (se configurado)

Coloque a pasta em `htdocs` e acesse via URL configurada.

### Opção C: Usar Nginx

Configure seu bloco de servidor para apontar para o diretório do projeto.

## 🎯 Primeiros Passos no Sistema

### 1. Acessar o Dashboard
- Abra `http://localhost:8000` no navegador
- Você verá um dashboard com resumo financeiro

### 2. Criar Categorias
- Clique em "Categorias" no menu
- Clique em "Nova Categoria"
- Preencha: Nome, Tipo (Receita/Despesa), Descrição
- Exemplo: "Supermercado", Despesa, "Compras de alimentos"

### 3. Adicionar Contas
- Clique em "Minhas Contas"
- Clique em "Nova Conta"
- Preencha: Nome, Descrição, Saldo Inicial
- Exemplo: "Conta Banco do Brasil", "Conta corrente", 1000.00

### 4. Registrar Transações
- Clique em "Transações"
- Clique em "Nova Transação"
- Preencha os campos:
  - Tipo: Receita ou Despesa
  - Categoria: Escolha uma categoria
  - Descrição: Detalhe da transação
  - Valor: Valor em reais
  - Data: Data da transação
  - Observações: Nota adicional (opcional)

### 5. Visualizar Relatórios
- Clique em "Relatórios"
- Veja gráficos e resumos por categoria
- Filtre por mês/ano

## 📊 Estrutura do Banco de Dados

### Tabela: categorias
```
id (int) - Identificador único
nome (varchar) - Nome da categoria
descricao (text) - Descrição
tipo (enum) - 'receita' ou 'despesa'
ativo (boolean) - Status ativo/inativo
data_criacao (timestamp) - Data de criação
```

### Tabela: transacoes
```
id (int) - Identificador único
categoria_id (int) - Referência à categoria
descricao (varchar) - Descrição da transação
valor (decimal) - Valor da transação
data_transacao (date) - Data da transação
tipo (enum) - 'receita' ou 'despesa'
status (enum) - 'pendente' ou 'concluido'
observacoes (text) - Notas adicionais
data_criacao (timestamp) - Data de criação
```

### Tabela: contas
```
id (int) - Identificador único
nome (varchar) - Nome da conta
descricao (text) - Descrição
saldo_inicial (decimal) - Saldo inicial
saldo_atual (decimal) - Saldo atual
ativa (boolean) - Status ativo/inativo
data_criacao (timestamp) - Data de criação
```

## 🔍 Troubleshooting

### Erro: "Erro na conexão"
**Solução:**
- Verifique se MySQL está rodando
- Confirme credenciais em `config/database.php`
- Teste: `mysql -u root -p` (no terminal)

### Erro: "Tabela não existe"
**Solução:**
- Execute: `php setup/create_tables.php`
- Verifique permissões de arquivo

### As categorias não aparecem
**Solução:**
- Verifique se `create_tables.php` foi executado
- Confirme dados em: `select * from categorias;` (MySQL)

### Página em branco
**Solução:**
- Ative exibição de erros em `config/database.php`
- Verifique log de erros do PHP
- Abra o console do navegador (F12)

## 🎨 Personalizações

### Mudar cores do tema
Edite `assets/css/style.css`:

```css
:root {
    --primary-color: #007bff;      /* Azul */
    --success-color: #28a745;      /* Verde */
    --danger-color: #dc3545;       /* Vermelho */
}
```

### Adicionar novas páginas
1. Crie `nova_pagina.php`
2. Inclua no início:
   ```php
   require_once __DIR__ . '/config/database.php';
   require_once __DIR__ . '/classes/Transacao.php';
   ```
3. Adicione link no menu de navegação (navbar)

## 📱 Recursos Implementados

✅ Dashboard com resumo financeiro
✅ CRUD completo de transações
✅ Gerenciamento de categorias
✅ Múltiplas contas
✅ Gráficos interativos
✅ Filtros por período
✅ Design responsivo
✅ Interface amigável
✅ Prepared statements (segurança)
✅ Dados de exemplo

## 🔐 Segurança

O sistema implementa:
- Prepared statements (previne SQL injection)
- Escape HTML (previne XSS)
- Validação de dados no servidor
- Proteção de arquivos via `.htaccess`

## 📚 Documentação Completa

Veja `README.md` para documentação detalhada.

## 💡 Dicas Úteis

1. **Backup do banco**: Use `mysqldump`
   ```bash
   mysqldump -u root -p contas_domesticas > backup.sql
   ```

2. **Restaurar backup**: 
   ```bash
   mysql -u root -p contas_domesticas < backup.sql
   ```

3. **Limpar dados**: No MySQL
   ```sql
   DELETE FROM transacoes;
   DELETE FROM categorias;
   DELETE FROM contas;
   ```

4. **Exportar para CSV**: Use ferramentas MySQL nativas ou implemente no sistema

## 🎓 Próximas Etapas

- Adicionar autenticação de usuários
- Implementar exportação CSV/PDF
- Adicionar metas de gastos
- Criar backup automático
- Adicionar importação de transações
- Criar aplicativo mobile

---

**Desenvolvido com ❤️ para ajudar na sua gestão financeira!**

Dúvidas? Verifique os logs ou abra o console do navegador (F12).
