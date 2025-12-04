# Instrucciones de Despliegue al Servidor

## Servidor: http://74.208.78.78:8080/

### Pasos para desplegar desde la rama `testing`:

#### 1. Conectarse al servidor vía SSH

```bash
ssh usuario@74.208.78.78
```

#### 2. Navegar al directorio del proyecto

```bash
cd /ruta/al/proyecto/ERP-IUM
```

#### 3. Verificar la rama actual

```bash
git branch
```

#### 4. Cambiar a la rama testing (si no está ya en ella)

```bash
git checkout testing
```

#### 5. Hacer pull de los cambios más recientes

```bash
git pull origin testing
```

#### 6. Verificar que los archivos se actualizaron correctamente

```bash
git log -1
ls -la
```

#### 7. Verificar permisos de archivos (si es necesario)

```bash
# Dar permisos al servidor web
sudo chown -R www-data:www-data .
sudo chmod -R 755 .
```

#### 8. Reiniciar servicios (si es necesario)

```bash
# Para Apache
sudo systemctl restart apache2

# O para Nginx + PHP-FPM
sudo systemctl restart nginx
sudo systemctl restart php8.1-fpm  # Ajustar versión de PHP según el servidor
```

#### 9. Verificar que el sitio funciona

- Abrir navegador en: http://74.208.78.78:8080/
- Probar el login
- Ir al Dashboard
- Hacer clic en el botón "Imprimir" de la comparativa

---

## Archivos modificados en este despliegue:

### Correcciones de la Comparativa del Dashboard:

1. **generate_comparativa_dashboard.php**

   - Función `strftime()` deprecada reemplazada por `IntlDateFormatter`
   - Variables de sesión corregidas (`user_id`, `user_nombre`)
   - BASE_URL definida correctamente
   - Mejoras de seguridad con `htmlspecialchars()`

2. **src/Dashboard/Views/dashboard.php**

   - URL de impresión corregida (ruta relativa directa)
   - Eliminado uso de BASE_URL en JavaScript

3. **index.php**
   - Eliminado código que bloqueaba archivos directos
   - Documentación mejorada

### Nuevos archivos de esquema de BD:

- database/schema/erp_ium_espejo_final.sql
- database/schema/erp_ium_final.sql
- database/schema/erp_ium_final_structure.sql

### Archivos del merge de presupuestos:

- GUIA_APP_OPTIMIZADO.md
- public/js/app.js
- src/Presupuestos/Controllers/PresupuestoController.php
- src/Presupuestos/Models/PresupuestoModel.php

---

## Verificación Post-Despliegue:

### ✅ Checklist:

- [ ] El sitio carga correctamente en http://74.208.78.78:8080/
- [ ] El login funciona
- [ ] El Dashboard carga sin errores
- [ ] El botón "Imprimir" de la comparativa abre una nueva pestaña
- [ ] El reporte se genera correctamente con los datos
- [ ] El formato de impresión se ve bien
- [ ] Las fechas aparecen en español
- [ ] El nombre del usuario aparece en el footer

### 🔍 En caso de errores:

#### Error 500:

```bash
# Ver logs de PHP
sudo tail -f /var/log/apache2/error.log
# O para Nginx
sudo tail -f /var/log/nginx/error.log
```

#### Extensión intl no instalada:

```bash
sudo apt-get install php-intl
sudo systemctl restart apache2  # o php-fpm
```

#### Permisos incorrectos:

```bash
sudo chown -R www-data:www-data /ruta/al/proyecto
sudo chmod -R 755 /ruta/al/proyecto
```

---

## Cambios Técnicos Principales:

### 1. PHP 8.1+ Compatibility

- Reemplazo de `strftime()` (deprecada) por `IntlDateFormatter`
- Uso de `DateTime` para manipulación de fechas

### 2. Corrección de Variables de Sesión

- `$_SESSION['id_user']` → `$_SESSION['user_id']`
- `$_SESSION['nombre_user']` → `$_SESSION['user_nombre']`

### 3. Enrutamiento Mejorado

- Archivos de generación ahora se acceden directamente
- Eliminada lógica problemática de detección en index.php

### 4. Seguridad

- Agregado `htmlspecialchars()` en outputs
- Validación de sesión mejorada
- Fallbacks para variables de sesión

---

## Rollback (en caso de problemas):

Si algo sale mal, puedes volver a la versión anterior:

```bash
git log --oneline -5  # Ver últimos 5 commits
git checkout [hash-del-commit-anterior]
# O crear una rama de respaldo
git checkout -b testing-rollback
git reset --hard origin/testing~1
git push origin testing-rollback
```

---

**Fecha de despliegue:** 24 de noviembre de 2025  
**Rama:** testing  
**Commit:** 8743625 (Merge: Resolver conflictos y agregar correcciones de comparativa dashboard)
