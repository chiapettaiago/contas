# Sistema de Registro Financeiro - Contas Domésticas

Um sistema completo e profissional de gestão financeira doméstica desenvolvido com PHP e MySQL.

## 🎯 Funcionalidades

### Dashboard
- Visualização geral de receitas, despesas e saldo
- Resumo por categoria
- Saldo total de contas
- Filtro por período (mês/ano)

### Gerenciamento de Transações
- Adicionar, editar e deletar transações
- Classificação por tipo (receita/despesa)
- Associação com categorias
- Data da transação
- Observações adicionais

### Categorias
- Gerenciar categorias de receita e despesa
- Descrição detalhada
- Ativar/desativar categorias
- Visualização separada por tipo

### Minhas Contas
- Gerenciar múltiplas contas
- Saldo inicial e saldo atual
- Visualização em cards
- Edição de saldos

### Relatórios
- Gráficos de receitas e despesas
- Análise mensal comparativa
- Resumo por categoria
- Composição visual de receitas vs despesas

## 📋 Requisitos

- PHP 7.4 ou superior
- MySQL 5.7 ou superior
- Servidor web (Apache, Nginx)

## 🚀 Instalação

### 1. Preparar o banco de dados

```bash
mysql -u root -p
CREATE DATABASE contas_domesticas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Configurar credenciais do banco

Edite o arquivo `config/database.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'seu_usuario');
define('DB_PASS', 'sua_senha');
define('DB_NAME', 'contas_domesticas');
```

### 3. Criar as tabelas

Execute o script de setup:

```bash
php setup/create_tables.php
```

Você deverá ver: `✓ Tabelas criadas com sucesso!`

### 4. Iniciar o servidor

```bash
cd /srv/compartilhada/Iago/projects/contas
php -S localhost:8000
```

Acesse em seu navegador: `http://localhost:8000`

## 📁 Estrutura do Projeto

```
contas/
├── index.php              # Dashboard principal
├── transacoes.php         # Gerenciar transações
├── categorias.php         # Gerenciar categorias
├── contas.php             # Gerenciar contas
├── relatorios.php         # Visualizar relatórios
├── config/
│   └── database.php       # Configuração do banco de dados
├── classes/
│   ├── Transacao.php      # Classe de transações
│   ├── Categoria.php      # Classe de categorias
│   └── Conta.php          # Classe de contas
├── setup/
│   └── create_tables.php  # Script para criar tabelas
└── assets/
    └── css/
        └── style.css      # Estilos personalizados
```

## 💻 Uso

### Dashboard
- Visualize um resumo de suas finanças
- Selecione mês e ano para filtrar dados
- Veja gráficos e estatísticas

### Adicionar Transação
1. Clique em "Nova Transação"
2. Selecione o tipo (receita/despesa)
3. Escolha a categoria
4. Preencha descrição, valor e data
5. Clique em "Salvar"

### Gerenciar Categorias
1. Acesse a seção "Categorias"
2. Crie novas categorias de receita e despesa
3. Edite ou desative conforme necessário

### Adicionar Conta
1. Clique em "Nova Conta"
2. Preencha nome, descrição e saldo inicial
3. Clique em "Salvar"
4. Use a seção de relatórios para atualizar saldos

## 🎨 Interface

- Design responsivo com Bootstrap 5
- Ícones FontAwesome
- Gráficos interativos com Chart.js
- Interface intuitiva e amigável

## 🔒 Segurança

- Prepared statements para prevenir SQL injection
- Escape de HTML para XSS
- Validação de dados no servidor

## 📊 Tecnologias Utilizadas

- **PHP 8+**: Linguagem backend
- **MySQL**: Banco de dados
- **Bootstrap 5**: Framework CSS
- **Chart.js**: Gráficos interativos
- **FontAwesome**: Ícones
- **JavaScript**: Interatividade frontend

## 📝 Exemplos de Uso

### Criar uma categoria de receita
1. Dashboard → Categorias
2. Clique em "Nova Categoria"
3. Nome: "Salário"
4. Tipo: "Receita"
5. Descrição: "Renda mensal"
6. Salvar

### Adicionar uma transação de despesa
1. Dashboard → Transações
2. Clique em "Nova Transação"
3. Tipo: "Despesa"
4. Categoria: "Alimentação"
5. Descrição: "Compra no supermercado"
6. Valor: 150.50
7. Data: 06/12/2025
8. Salvar

### Visualizar relatório mensal
1. Dashboard → Relatórios
2. Selecione o mês e ano desejado
3. Visualize gráficos e resumos
4. Analise gastos por categoria

## 🐛 Troubleshooting

### Erro "Tabelas não criadas"
- Verifique se o banco de dados foi criado
- Confirme as credenciais em `config/database.php`
- Execute novamente `php setup/create_tables.php`

### Erro "Conexão recusada"
- Verifique se MySQL está rodando
- Confirme localhost/credenciais
- Teste a conexão: `mysql -u root -p`

### Gráficos não aparecem
- Verifique conexão com CDN Chart.js
- Inspione o console do navegador

## 📧 Suporte

Para problemas ou sugestões, verifique:
- Permissões de arquivo
- Versão do PHP (`php -v`)
- Versão do MySQL (`mysql --version`)

## 📄 Licença

Livre para uso pessoal e comercial.

---

**Desenvolvido com ❤️ para gestão financeira doméstica**
