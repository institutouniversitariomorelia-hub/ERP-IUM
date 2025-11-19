# 📊 Bases de Datos Definitivas - ERP IUM
**Fecha de exportación:** 19 de Noviembre, 2025  
**Versión:** 2.0 (Con todas las mejoras implementadas)

---

## 📁 Contenido de esta Carpeta

### 1. `erp_ium.sql` (231 KB)
**Base de datos principal de producción**

Incluye todas las tablas, datos y estructura actualizada con:
- ✅ Campo `nombre` en tabla `presupuestos`
- ✅ FK constraint `fk_presupuestos_parent` con CASCADE
- ✅ Sistema de pagos divididos (`pagos_parciales` table)
- ✅ Estructura jerárquica de presupuestos (General → Sub-presupuestos)
- ✅ Datos actuales de usuarios, categorías, ingresos, egresos
- ✅ Registros de auditoría

### 2. `erp_ium_espejo.sql` (149 KB)
**Base de datos espejo (mirror) para respaldos**

Réplica exacta de `erp_ium` con la misma estructura y datos.

---

## 🚀 Características Implementadas

### Sistema de Presupuestos v2.0
- **Campo nombre:** Identificación descriptiva de presupuestos
- **Jerarquía:** Presupuestos generales → Sub-presupuestos por categoría
- **Integridad:** FK constraints previenen registros huérfanos
- **Alertas:** Detección automática de presupuestos >=90% consumidos

### Sistema de Pagos Divididos
- **Tabla pagos_parciales:** Almacena múltiples métodos de pago por ingreso
- **Métodos soportados:** Efectivo, Transferencia, Cheque, Tarjeta, Mixto
- **Validación:** Suma de pagos parciales debe igual el monto total

### Auditoría Completa
- **Triggers automáticos:** Registran todos los INSERT, UPDATE, DELETE
- **Trazabilidad:** Usuario, fecha, acción, valores anteriores/nuevos
- **Cobertura:** Usuarios, categorías, presupuestos, ingresos, egresos

---

## 📋 Estructura de Tablas

| Tabla | Descripción | Registros Clave |
|-------|-------------|-----------------|
| `usuarios` | Gestión de usuarios y roles | Admin, colaboradores |
| `categorias` | Clasificación de ingresos/egresos | ~10 categorías |
| `presupuestos` | Sistema jerárquico de presupuestos | General + Sub-presupuestos |
| `ingresos` | Registro de ingresos con pagos divididos | Con folios |
| `pagos_parciales` | Desglose de métodos de pago | Por ingreso |
| `egresos` | Registro de gastos vinculados a presupuestos | Con proveedores |
| `auditoria` | Log completo de acciones del sistema | Histórico |

---

## 🔄 Cómo Importar

### Opción 1: phpMyAdmin
1. Accede a phpMyAdmin (http://localhost/phpmyadmin)
2. Crea las bases de datos:
   ```sql
   CREATE DATABASE IF NOT EXISTS erp_ium;
   CREATE DATABASE IF NOT EXISTS erp_ium_espejo;
   ```
3. Selecciona cada base de datos
4. Ve a la pestaña "Importar"
5. Selecciona el archivo SQL correspondiente
6. Click en "Continuar"

### Opción 2: Línea de Comandos
```bash
# Importar base principal
mysql -uroot erp_ium < erp_ium.sql

# Importar base espejo
mysql -uroot erp_ium_espejo < erp_ium_espejo.sql
```

### Opción 3: PowerShell (Windows/XAMPP)
```powershell
# Importar ambas bases de datos
C:\xampp\mysql\bin\mysql -uroot erp_ium < database_final\erp_ium.sql
C:\xampp\mysql\bin\mysql -uroot erp_ium_espejo < database_final\erp_ium_espejo.sql
```

---

## ⚙️ Configuración Post-Importación

### 1. Verificar conexión en `db.php`
```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');           // Ajustar si tienes contraseña
define('DB_NAME', 'erp_ium');    // Base principal
```

### 2. Usuario por defecto
```
Usuario: su_admin
Contraseña: admin123
Rol: Administrador
```

### 3. Verificar estructura
```sql
-- Verificar campo nombre en presupuestos
DESCRIBE presupuestos;

-- Verificar FK constraint
SELECT CONSTRAINT_NAME, UPDATE_RULE, DELETE_RULE
FROM information_schema.REFERENTIAL_CONSTRAINTS
WHERE CONSTRAINT_NAME = 'fk_presupuestos_parent'
  AND CONSTRAINT_SCHEMA = 'erp_ium';

-- Verificar tabla de pagos divididos
SHOW TABLES LIKE 'pagos_parciales';
```

---

## 📊 Datos Incluidos

### Usuarios
- 1 Administrador (su_admin)
- Usuarios de prueba con diferentes roles

### Categorías
- Colegiaturas (Licenciatura, Posgrado)
- Nómina Administrativa
- Mantenimiento Campus
- Servicios Básicos
- Papelería y Oficina
- Y más...

### Presupuestos
- Presupuestos generales mensuales
- Sub-presupuestos por categoría
- Datos con campo `nombre` descriptivo
- Montos gastados calculados

### Transacciones
- Ingresos con pagos únicos y divididos
- Egresos vinculados a presupuestos
- Proveedores y destinatarios
- Documentos de amparo

---

## 🔐 Seguridad

### Contraseñas
Todas las contraseñas están hasheadas con `password_hash()` (bcrypt).

### Triggers de Auditoría
Todos los cambios son registrados automáticamente con:
- ID de usuario
- Fecha y hora
- Acción realizada
- Valores anteriores y nuevos

### Integridad Referencial
- FK constraints con CASCADE previenen inconsistencias
- Validaciones en modelos PHP
- Transacciones para operaciones críticas

---

## 📝 Migraciones Aplicadas

1. ✅ `2025-11-07_presupuesto_categoria.sql` - Campo id_categoria
2. ✅ `2025-11-12_presupuesto_parent.sql` - Campo parent_presupuesto
3. ✅ `2025-11-18_pagos_divididos.sql` - Sistema de pagos parciales
4. ✅ `add_nombre_to_presupuestos.sql` - Campo nombre descriptivo
5. ✅ `fix_orphaned_and_add_fk.sql` - FK constraint + limpieza

---

## 🆘 Solución de Problemas

### Error: "Table doesn't exist"
```sql
-- Verificar que las tablas existan
SHOW TABLES;
```

### Error: "Definer does not exist"
Si tienes problemas con triggers/procedimientos, ejecuta:
```sql
-- Eliminar triggers problemáticos
DROP TRIGGER IF EXISTS trg_ingresos_after_insert_aud;
-- Re-crearlos con el usuario correcto
```

### Error: "Duplicate entry"
```sql
-- Limpiar base antes de importar
DROP DATABASE IF EXISTS erp_ium;
CREATE DATABASE erp_ium;
```

---

## 📞 Soporte

Para problemas o dudas:
1. Revisa los logs de auditoría: `SELECT * FROM auditoria ORDER BY fecha DESC LIMIT 50;`
2. Verifica la configuración en `db.php`
3. Consulta la documentación en `README.md` del proyecto

---

## 🎯 Próximos Pasos

1. ✅ Importar bases de datos
2. ✅ Verificar usuario admin funciona
3. ✅ Probar módulo de presupuestos (crear general + sub-presupuestos)
4. ✅ Probar módulo de ingresos (pago único y dividido)
5. ✅ Probar módulo de egresos (vinculación con presupuestos)
6. ✅ Revisar alertas de presupuestos
7. ✅ Verificar auditoría registra acciones

---

**¡Sistema listo para producción! 🚀**
