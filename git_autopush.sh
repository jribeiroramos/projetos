#!/bin/bash
# Script para commit/push automático do projeto

# Configurações
DIR="/var/www/html/projetos"
BRANCH="main"
MSG="Auto-commit diário em $(date '+%Y-%m-%d %H:%M:%S')"

cd "$DIR" || exit 1

# Atualiza remoto (evita conflitos)
git pull --rebase origin $BRANCH

# Verifica se há mudanças
if [[ -n "$(git status --porcelain)" ]]; then
    git add -A
    git commit -m "$MSG"
    git push origin $BRANCH
    echo "[OK] Alterações enviadas: $MSG"
else
    echo "[OK] Nenhuma alteração detectada em $DIR"
fi
