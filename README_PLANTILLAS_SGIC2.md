# Plantillas Refactorizadas para SGIC 2.0

## Resumen Ejecutivo

Se han refactorizado exitosamente **6 plantillas .dotx** para adaptarlas al sistema **SGIC 2.0**, convirtiendo los marcadores antiguos `[CAMPO]` al formato Jinja2 `{{CAMPO}}` compatible con `python-docx-template`.

---

## Archivos Generados

| Archivo Original | Archivo SGIC 2.0 | Estado |
|-----------------|------------------|--------|
| Plantilla_ActaInhumacion.dotx | Plantilla_ActaInhumacion_SGIC2.dotx | ✅ Listo |
| Plantilla_CambioCelular.dotx | Plantilla_CambioCelular_SGIC2.dotx | ✅ Listo |
| Plantilla_ContratoCesioDerechos.dotx | Plantilla_ContratoCesioDerechos_SGIC2.dotx | ✅ Listo |
| Plantilla_Pagare.dotx | Plantilla_Pagare_SGIC2.dotx | ✅ Listo |
| Plantilla_ReciboPago.dotx | Plantilla_ReciboPago_SGIC2.dotx | ✅ Listo |
| Plantilla_TituloPropiedad.dotx | Plantilla_TituloPropiedad_SGIC2.dotx | ✅ Listo |

---

## Marcadores por Documento

### 1. Acta de Inhumación (10 campos únicos)
```
{{AÑO_ACTUAL}}, {{DIA_ACTUAL}}, {{DIRECCION_PARROQUIA}}, {{FECHA_ACTUAL}}, 
{{FECHA_TRAMITE}}, {{MES_ACTUAL}}, {{NOMBRE_PARROQUIA}}, {{NUMERO_TRAMITE}}, 
{{RFC_PARROQUIA}}, {{TELEFONO_PARROQUIA}}
```

### 2. Cambio de Celular (4 campos únicos)
```
{{DIRECCION_PARROQUIA}}, {{NOMBRE_PARROQUIA}}, {{RFC_PARROQUIA}}, {{TELEFONO_PARROQUIA}}
```

### 3. Contrato de Cesión de Derechos (6 campos únicos)
```
{{AÑO_ACTUAL}}, {{CIUDAD_PARROQUIA}}, {{DIA_ACTUAL}}, {{MES_ACTUAL}}, 
{{NOMBRE_PARROQUIA}}, {{NOMBRE_SOLICITANTE}}
```

### 4. Pagaré (6 campos únicos)
```
{{CIUDAD_PARROQUIA}}, {{DIRECCION_PARROQUIA}}, {{FECHA_ACTUAL}}, 
{{MONTO_PAGO}}, {{MONTO_PAGO_LETRA}}, {{NOMBRE_PARROQUIA}}
```

### 5. Recibo de Pago (8 campos únicos)
```
{{DIRECCION_PARROQUIA}}, {{DIRECCION_SOLICITANTE}}, {{MONTO_PAGO}}, 
{{NOMBRE_PARROQUIA}}, {{NOMBRE_SOLICITANTE}}, {{NUMERO_RECIBO}}, 
{{RFC_PARROQUIA}}, {{TELEFONO_PARROQUIA}}
```

### 6. Título de Propiedad (12 campos únicos)
```
{{AÑO_ACTUAL}}, {{CIUDAD_PARROQUIA}}, {{DIA_ACTUAL}}, {{DIRECCION_PARROQUIA}}, 
{{DOCUMENTO_IDENTIDAD}}, {{FECHA_EXPEDICION}}, {{MES_ACTUAL}}, {{NOMBRE_PARROQUIA}}, 
{{NOMBRE_SOLICITANTE}}, {{NUMERO_TITULO}}, {{RFC_PARROQUIA}}, {{TELEFONO_PARROQUIA}}
```

---

## Mapeo de Campos Completado

### Campos Generales (Tenant/Parroquia)
| Campo SGIC 2.0 | Descripción |
|---------------|-------------|
| `NOMBRE_PARROQUIA` | Nombre del Tenant / Parroquia |
| `DIRECCION_PARROQUIA` | Dirección oficial de la parroquia |
| `TELEFONO_PARROQUIA` | Teléfono de contacto oficial |
| `RFC_PARROQUIA` | RFC de la parroquia |
| `CIUDAD_PARROQUIA` | Ciudad donde está ubicada |

### Datos del Solicitante
| Campo SGIC 2.0 | Descripción |
|---------------|-------------|
| `NOMBRE_SOLICITANTE` | Nombre completo del ciudadano |
| `DIRECCION_SOLICITANTE` | Dirección del solicitante |
| `DOCUMENTO_IDENTIDAD` | CURP/DNI/Cédula del solicitante |

### Datos del Trámite
| Campo SGIC 2.0 | Descripción |
|---------------|-------------|
| `FECHA_ACTUAL` | Fecha de generación del documento |
| `FECHA_TRAMITE` | Fecha específica del trámite |
| `FECHA_EXPEDICION` | Fecha de expedición del título |
| `NUMERO_TRAMITE` | Folio/número de trámite |
| `NUMERO_RECIBO` | Número de recibo de pago |
| `NUMERO_TITULO` | Número de título de propiedad |
| `MONTO_PAGO` | Monto numérico del pago |
| `MONTO_PAGO_LETRA` | Monto escrito en letras |

### Variables de Fecha Automáticas
| Campo SGIC 2.0 | Descripción |
|---------------|-------------|
| `DIA_ACTUAL` | Día del mes (1-31) |
| `MES_ACTUAL` | Mes en texto (Enero, Febrero, etc.) |
| `AÑO_ACTUAL` | Año en curso (2025) |

---

## Instrucciones de Uso

### Instalación de Dependencias
```bash
pip install docxtpl
```

### Ejemplo de Código Python

```python
from docxtpl import DocxTemplate
from datetime import datetime

# ============================================
# EJEMPLO 1: Generar Recibo de Pago
# ============================================
def generar_recibo_pago(datos):
    tpl = DocxTemplate('Plantilla_ReciboPago_SGIC2.dotx')
    
    context = {
        'NOMBRE_PARROQUIA': datos['parroquia_nombre'],
        'DIRECCION_PARROQUIA': datos['parroquia_direccion'],
        'TELEFONO_PARROQUIA': datos['parroquia_telefono'],
        'RFC_PARROQUIA': datos['parroquia_rfc'],
        'NOMBRE_SOLICITANTE': datos['cliente_nombre'],
        'DIRECCION_SOLICITANTE': datos['cliente_direccion'],
        'MONTO_PAGO': f"${datos['monto']:,.2f}",
        'NUMERO_RECIBO': datos['numero_recibio'],
    }
    
    tpl.render(context)
    tpl.save(f"Recibo_{datos['numero_recibio']}.docx")
    print(f"✓ Recibo generado: Recibo_{datos['numero_recibio']}.docx")

# ============================================
# EJEMPLO 2: Generar Acta de Inhumación
# ============================================
def generar_acta_inhumacion(datos):
    tpl = DocxTemplate('Plantilla_ActaInhumacion_SGIC2.dotx')
    
    context = {
        'NOMBRE_PARROQUIA': datos['parroquia_nombre'],
        'DIRECCION_PARROQUIA': datos['parroquia_direccion'],
        'TELEFONO_PARROQUIA': datos['parroquia_telefono'],
        'RFC_PARROQUIA': datos['parroquia_rfc'],
        'FECHA_ACTUAL': datetime.now().strftime('%d de %B de %Y'),
        'FECHA_TRAMITE': datos['fecha_inhumacion'],
        'NUMERO_TRAMITE': datos['folio_acta'],
        'DIA_ACTUAL': str(datetime.now().day),
        'MES_ACTUAL': datetime.now().strftime('%B'),
        'AÑO_ACTUAL': str(datetime.now().year),
    }
    
    tpl.render(context)
    tpl.save(f"Acta_{datos['folio_acta']}.docx")
    print(f"✓ Acta generada: Acta_{datos['folio_acta']}.docx")

# ============================================
# EJEMPLO 3: Generar Título de Propiedad
# ============================================
def generar_titulo_propiedad(datos):
    tpl = DocxTemplate('Plantilla_TituloPropiedad_SGIC2.dotx')
    
    context = {
        'NOMBRE_PARROQUIA': datos['parroquia_nombre'],
        'DIRECCION_PARROQUIA': datos['parroquia_direccion'],
        'TELEFONO_PARROQUIA': datos['parroquia_telefono'],
        'RFC_PARROQUIA': datos['parroquia_rfc'],
        'CIUDAD_PARROQUIA': datos['ciudad'],
        'NOMBRE_SOLICITANTE': datos['cliente_nombre'],
        'DOCUMENTO_IDENTIDAD': datos['cliente_curp'],
        'FECHA_EXPEDICION': datos['fecha_expedicion'],
        'NUMERO_TITULO': datos['folio_titulo'],
        'DIA_ACTUAL': str(datetime.now().day),
        'MES_ACTUAL': datetime.now().strftime('%B'),
        'AÑO_ACTUAL': str(datetime.now().year),
    }
    
    tpl.render(context)
    tpl.save(f"Titulo_{datos['folio_titulo']}.docx")
    print(f"✓ Título generado: Titulo_{datos['folio_titulo']}.docx")

# ============================================
# DATOS DE EJEMPLO
# ============================================
if __name__ == '__main__':
    # Datos comunes de la parroquia (tenant)
    parroquia = {
        'parroquia_nombre': 'Parroquia San José',
        'parroquia_direccion': 'Av. Principal #123, Centro',
        'parroquia_telefono': '(555) 123-4567',
        'parroquia_rfc': 'PSJ123456789',
        'ciudad': 'Ciudad de México',
    }
    
    # Generar recibo de pago
    datos_recibo = {
        **parroquia,
        'cliente_nombre': 'Juan Pérez García',
        'cliente_direccion': 'Calle 5 #456, Col. Centro',
        'monto': 1500.00,
        'numero_recibio': 'REC-2025-001234',
    }
    generar_recibo_pago(datos_recibo)
    
    # Generar acta de inhumación
    datos_acta = {
        **parroquia,
        'fecha_inhumacion': '15 de Enero de 2025',
        'folio_acta': 'ACT-2025-000567',
    }
    generar_acta_inhumacion(datos_acta)
    
    # Generar título de propiedad
    datos_titulo = {
        **parroquia,
        'cliente_nombre': 'María López Hernández',
        'cliente_curp': 'LOHM850101MDFPRR09',
        'fecha_expedicion': '15 de Enero de 2025',
        'folio_titulo': 'TIT-2025-000890',
    }
    generar_titulo_propiedad(datos_titulo)
```

---

## Campos Pendientes de Mapeo

Los siguientes campos fueron identificados en las plantillas originales pero requieren definición adicional según las necesidades específicas de cada documento:

### Para Acta de Inhumación
- `NOMBRE_FALLECIDO`, `FECHA_NACIMIENTO_FALLECIDO`, `FECHA_DEFUNCION`
- `CAUSA_DEFUNCION`, `LUGAR_DEFUNCION`, `NOMBRE_FAMILIAR_RESPONSABLE`
- `TESTIGO1_NOMBRE`, `TESTIGO2_NOMBRE`, `SACERDOTE_OFICIAL`

### Para Contrato de Cesión
- `NUMERO_LOTE`, `SECCION_CEMENTERIO`, `TIPO_SERVICIO`
- `DURACION_ANOS`, `MONTO_ANUAL`, `BENEFICIARIO_1`, `BENEFICIARIO_2`

### Para Pagaré
- `NOMBRE_DEUDOR`, `DOMICILIO_DEUDOR`, `CELULAR_DEUDOR`, `RFC_DEUDOR`
- `CONCEPTO`, `FECHA_VENCIMIENTO`, `PLAZO`, `TASA_MORATORIA`

### Para Recibo de Pago
- `CONCEPTO_PAGO`, `MONTO_CAPITAL`, `MONTO_IVA`, `MONTO_MORATORIO`
- `CELULAR_CLIENTE`, `CORREO_CLIENTE`, `REFERENCIA_RECIBO`

**Nota:** Estos campos pueden agregarse al script `refactorizar_plantillas_sgic2.py` en el diccionario `CAMPOS_MAPEO` según se requiera.

---

## Script de Refactorización

El archivo `refactorizar_plantillas_sgic2.py` contiene:
- ✅ Mapeo completo de campos antiguos a nuevos
- ✅ Procesamiento automático de todas las plantillas
- ✅ Detección de campos no mapeados
- ✅ Generación de archivos *_SGIC2.dotx

Para ejecutarlo:
```bash
python3 refactorizar_plantillas_sgic2.py
```

---

## Próximos Pasos Recomendados

1. **Validar las plantillas generadas** abriéndolas en Microsoft Word o LibreOffice
2. **Agregar campos específicos** según los requisitos de cada tipo de documento
3. **Integrar con SGIC 2.0** usando los ejemplos de código proporcionados
4. **Realizar pruebas de fusión** con datos reales del sistema
5. **Configurar la conversión a PDF** si se requiere entrega en ese formato

---

## Soporte Técnico

Para agregar más campos al mapeo, editar el diccionario `CAMPOS_MAPEO` en el script:

```python
CAMPOS_MAPEO = {
    'CAMPO_ANTIGUO': 'NUEVO_CAMPO_SGIC2',
    # Agregar aquí nuevos mapeos...
}
```

Luego volver a ejecutar el script para regenerar las plantillas.
