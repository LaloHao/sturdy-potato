#!/usr/bin/env bash

# Ejecutar servidor de Laravel en todas las interfaces (0.0.0.0)
php artisan serve --host=0.0.0.0  &
# Ejecutar servidor Vite en todas las interfaces
npm run dev -- --host=0.0.0.0

wait
exit $?
