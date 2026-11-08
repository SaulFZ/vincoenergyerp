# Catálogos iniciales de SGC beta

Esta carga pertenece exclusivamente a tablas nuevas de VesCore. No consulta la carpeta de beta al ejecutar las migraciones y no crea datos desde el formulario de servicios.

## Tablas y registros incluidos

| Tabla nueva | Registros | Origen |
| --- | ---: | --- |
| clients | 24 | Client |
| equipment | 55 | Asset: ECP, GENERADOR, CAJA y CHIP |
| tool_families | 20 | 13 filas de ToolFamily y 7 opciones adicionales del formulario de herramientas |
| tools | 312 | LabTool, fuente consultada por el alta de OS |
| service_types | 30 | Maestro ServiceType; independiente de services de RH |
| cables | 121 | Cable |
| training_courses | 259 | TrainingCourse |
| employee_training_records | 0 | Preparada para futuras asignaciones a empleados existentes de VesCore |

Total: 821 registros reales o valores de catálogo existentes en beta.

## Convenciones y relaciones

- PK autoincremental `id`, columnas en snake_case y `created_at` / `updated_at`.
- `legacy_id` conserva el ID de origen; no es el ID de una tabla existente de VesCore.
- `source = sgc_beta` identifica la carga inicial; los registros futuros usan `manual` por defecto.
- `tools.tool_family_id` se resuelve con los IDs nuevos de `tool_families`.
- `cables.current_unit_id` queda NULL. Se conserva `current_unit_label` como referencia textual del origen. La vinculación debe realizarla un usuario usando una unidad real de `ves_units`.
- `employee_training_records` referencia `employees` y `training_courses`, sin alterar sus tablas padre. No se importan los 6972 registros de capacitación de beta porque falta la correspondencia de personas con los empleados de VesCore.
- Los nombres de cursos no llevan unicidad: beta tiene variantes de mayúsculas y acentos que colisionan en una collation MySQL insensible a esas diferencias. Se conserva cada curso por su `legacy_id`, sin fusionarlos automáticamente.
- Se conserva el contenido de `brand` en herramientas tal como estaba en beta; allí a veces se utilizaba para indicar familia. La FK de familia queda separada.
- Las fechas de origen se convierten a UTC en formato de base de datos; cuando no existen se usa la fecha de ejecución de la carga.

## Exclusiones

No se modifican ni cargan `ves_units`, `employees`, `wells`, `squads`, `services` de RH ni otras tablas existentes. No se copian vehículos al catálogo equipment. No se mezclan ToolCatalog y LabTool ni se convierten resúmenes históricos de OS en tipos de servicio.

No se incluyen órdenes de servicio, corridas, evidencias, mantenimiento ni certificaciones: requieren su implementación de backend posterior. Esta entrega solo prepara los catálogos y la tabla de asignación de cursos.

## Ejecución

Las ocho migraciones `2026_11_08_000001` a `2026_11_08_000008` crean las tablas. La `2026_11_08_000009_import_sgc_beta_catalogs` importa estos JSON una sola vez dentro de una transacción. No requiere ejecutar DatabaseSeeder.

Para ejecutar únicamente esta entrega, usar Artisan con un `--path` por cada uno de esos nueve archivos, respetando sus nombres. No ejecutar migraciones antiguas pendientes sin revisarlas.

La reversión de la carga elimina solo filas con `source = sgc_beta`; las migraciones de estructura eliminan únicamente las tablas nuevas. Las claves foráneas impiden retirar catálogos que ya tengan asignaciones dependientes.
