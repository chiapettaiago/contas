#!/bin/bash
# Script de instalação rápida do Sistema de Contas Domésticas

echo "======================================"
echo "Instalação do Sistema de Contas"
echo "======================================"
echo ""

# Cores para output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Verificar PHP
echo "Verificando PHP..."
if ! command -v php &> /dev/null; then
    echo -e "${RED}✗ PHP não encontrado. Instale PHP 7.4+${NC}"
    exit 1
fi
PHP_VERSION=$(php -v | head -n 1)
echo -e "${GREEN}✓ $PHP_VERSION${NC}"

# Verificar MySQL
echo ""
echo "Verificando MySQL..."
if ! command -v mysql &> /dev/null; then
    echo -e "${RED}✗ MySQL não encontrado. Instale MySQL 5.7+${NC}"
    exit 1
fi
MYSQL_VERSION=$(mysql --version)
echo -e "${GREEN}✓ $MYSQL_VERSION${NC}"

# Criar banco de dados
echo ""
echo "Criar banco de dados?"
echo "Execute no seu cliente MySQL:"
echo ""
echo -e "${YELLOW}CREATE DATABASE contas_domesticas CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;${NC}"
echo ""
read -p "Pressione ENTER quando o banco de dados for criado..."

# Configurar database.php
echo ""
echo "Configurando conexão ao banco..."
echo ""
echo "Edite o arquivo: config/database.php"
echo "E configure:"
echo "  DB_HOST: localhost"
echo "  DB_USER: seu_usuario_mysql"
echo "  DB_PASS: sua_senha_mysql"
echo ""
read -p "Pressione ENTER quando terminar de editar..."

# Executar script de criação de tabelas
echo ""
echo "Criando tabelas..."
php setup/create_tables.php

if [ $? -eq 0 ]; then
    echo ""
    echo -e "${GREEN}======================================"
    echo "Instalação concluída com sucesso!"
    echo "======================================${NC}"
    echo ""
    echo "Para iniciar o servidor:"
    echo -e "${YELLOW}php -S localhost:8000${NC}"
    echo ""
    echo "Acesse em seu navegador:"
    echo -e "${YELLOW}http://localhost:8000${NC}"
else
    echo -e "${RED}Erro na criação das tabelas${NC}"
    exit 1
fi
