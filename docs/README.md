# 🏢 ERP-IUM - Sistema de Gestión Financiera

**Instituto Universitario Morelia**  
Sistema completo de Ingresos, Egresos, Presupuestos y Reportes

---

## 📁 Estructura del Proyecto

```
ERP-IUM/
├── config/                    # Configuración del sistema
│   └── database.php          # Conexión a BD
│
├── src/                      # Código fuente (por módulos)
│   ├── Auth/                 # Autenticación y usuarios
│   ├── Ingresos/             # Gestión de ingresos
│   ├── Egresos/              # Gestión de egresos
│   ├── Categorias/           # Categorías del sistema
│   ├── Presupuestos/         # Control presupuestal
│   ├── Reportes/             # Reportes y dashboards
│   ├── Auditoria/            # Registro de auditoría
│   └── Dashboard/            # Panel principal
│
├── shared/                   # Recursos compartidos
│   ├── Views/                # Layout y vistas comunes
│   └── Helpers/              # Funciones auxiliares
│
├── database/                 # Base de datos
│   ├── migrations/           # Migraciones SQL
│   │   ├── 00_active/       # Activas (usar)
│   │   ├── 01_deprecated/   # Obsoletas (historial)
│   │   └── 02_maintenance/  # Mantenimiento
│   ├── schema/              # Esquemas finales
│   └── backups/             # Respaldos
│
├── public/                   # Archivos públicos
│   ├── css/
│   ├── js/
│   └── images/
│
├── utils/                    # Utilidades
│   ├── diagnostico.php      # Script de diagnóstico
│   └── password.php         # Generador de hashes
│
└── docs/                     # Documentación
    └── CHANGELOG_NOVIEMBRE_2025.md
```

---

## 🚀 Instalación

### Requisitos
- PHP 7.4+
- MySQL 5.7+
- XAMPP/WAMP (recomendado)

### Pasos

1. **Clonar repositorio**
```bash
git clone https://github.com/institutouniversitariomorelia-hub/ERP-IUM.git
cd ERP-IUM
```

2. **Configurar base de datos**
```bash
# Importar schema principal
mysql -u root < database/schema/erp_ium.sql

# Importar schema espejo
mysql -u root < database/schema/erp_ium_espejo.sql

# Aplicar migraciones activas
mysql -u root erp_ium < database/migrations/00_active/2025-11-20_refactor_categorias.sql
mysql -u root erp_ium < database/migrations/00_active/insert_categorias_predefinidas.sql
# ... resto de migraciones
```

3. **Configurar conexión**
Editar `config/database.php`:
```php
$host = 'localhost';
$dbname = 'erp_ium';
$username = 'root';
$password = '';
```

4. **Acceder al sistema**
```
http://localhost/ERP-IUM/
```

---

## 📚 Módulos del Sistema

### 🔐 Auth
- Login/Logout
- Gestión de usuarios
- Perfiles

### 💰 Ingresos
- Registro de ingresos
- 3 tipos de recibos diferenciados:
  - Registro Diario
  - Titulaciones
  - Inscripciones/Reinscripciones

### 💸 Egresos
- Registro de egresos
- Comprobantes de egreso
- Control de proveedores

### 🏷️ Categorías
- 41 categorías predefinidas protegidas
- 30 categorías de egreso
- 11 categorías de ingreso

### 📊 Presupuestos
- Control presupuestal
- Seguimiento de gastos

### 📈 Reportes
- Reporte de ingresos
- Reporte de egresos
- Reporte consolidado
- Reporte de auditoría
- Dashboard comparativo

### 🔍 Auditoría
- Registro de todas las acciones
- Trazabilidad completa

---

## 🗄️ Base de Datos

### Migraciones

#### ✅ Activas (00_active)
- `2025-11-20_refactor_categorias.sql` - Refactorización categorías
- `2025-11-21_remove_concepto_from_ingresos.sql` - Limpieza ingresos
- `2025-11-21_remove_activo_fijo_from_egresos.sql` - Limpieza egresos
- `2025-11-21_fix_triggers_ingresos_egresos.sql` - 12 triggers actualizados
- `insert_categorias_predefinidas.sql` - 41 categorías del sistema

#### ⚠️ Obsoletas (01_deprecated)
Mantener solo para historial, NO ejecutar en nuevas instalaciones

#### 🔧 Mantenimiento (02_maintenance)
- `limpieza_total.sql` - Reset de datos
- `seed_realistic_data.sql` - Datos de prueba

---

## 🎨 Sistema de Recibos

### Formato
- **Tamaño:** 8.5" x 5.5" (horizontal)
- **Diseño:** Flexbox responsive
- **Marca de agua:** Reimpresión automática

### Tipos
1. **Ingreso - Registro Diario**
2. **Ingreso - Titulaciones**
3. **Ingreso - Inscripciones/Reinscripciones**
4. **Egreso**
5. **Recibo en Blanco**

---

## 🛠️ Desarrollo

### Estructura MVC por Módulo
Cada módulo sigue esta estructura:
```
src/[Modulo]/
├── Controllers/     # Lógica de negocio
├── Models/         # Acceso a datos
├── Views/          # Interfaz de usuario
└── [Extras]/       # Receipts, Generators, etc.
```

### Agregar Nuevo Módulo
1. Crear carpeta en `src/[NuevoModulo]/`
2. Crear subcarpetas: Controllers, Models, Views
3. Seguir convenciones existentes

---

## 📝 Convenciones

### Archivos
- Controllers: `[Nombre]Controller.php`
- Models: `[Nombre]Model.php`
- Views: `[nombre]_list.php` o `[nombre].php`

### Base de Datos
- Tablas: plural minúsculas (`ingresos`, `egresos`)
- Columnas: snake_case (`id_categoria`, `folio_ingreso`)
- Foreign Keys: `id_[tabla_singular]`

---

## 🔒 Seguridad

- ✅ Prepared statements (mysqli)
- ✅ Validación de sesiones
- ✅ Sanitización de inputs
- ✅ Control de acceso por roles

---

## 📌 Notas Importantes

### Categorías Protegidas
NO eliminar categorías con `no_borrable = 1` (las 41 predefinidas)

### Base de Datos Espejo
Mantener sincronizada `erp_ium_espejo` con principal

### Triggers
12 triggers activos (6 ingresos + 6 egresos)

---

## 🐛 Diagnóstico

Ejecutar script de diagnóstico:
```bash
php utils/diagnostico.php
```

Verifica:
- Conexión a BD
- Integridad de tablas
- Triggers activos
- Categorías protegidas

---

## 👥 Equipo

**Desarrollado por:** Instituto Universitario Morelia  
**Branch:** testing  
**Última actualización:** Noviembre 2025

---

## 📄 Licencia

Uso interno del Instituto Universitario Morelia

---

## 🆘 Soporte

Para soporte técnico, contactar al departamento de TI del IUM.

---

**Versión:** 2.0 (Post-refactorización)
