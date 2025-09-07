# Projetos App (PHP + MySQL)

App simples para registrar ideias/projetos com **status**, **partes interessadas** e datas. Inclui login, CRUD, relatórios e exportação CSV.

## Stack
- PHP 8+, Apache (mod_php)
- MySQL/MariaDB
- Bootstrap 5

## Setup
1. Crie DB e usuário:
   ```sql
   CREATE DATABASE projetos_app CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   CREATE USER 'projetos'@'localhost' IDENTIFIED BY 'projetos';
   GRANT ALL PRIVILEGES ON projetos_app.* TO 'projetos'@'localhost';
   FLUSH PRIVILEGES;
