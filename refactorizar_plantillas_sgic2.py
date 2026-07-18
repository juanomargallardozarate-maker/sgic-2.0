#!/usr/bin/env python3
"""
Script para refactorizar plantillas .dotx y adaptarlas al sistema SGIC 2.0
Reemplaza marcadores [CAMPO] por {{CAMPO}} compatibles con python-docx-template
"""

import zipfile
import xml.etree.ElementTree as ET
from pathlib import Path
import re
import shutil
import os

# Mapeo de campos antiguos a nuevos campos SGIC 2.0
CAMPOS_MAPEO = {
    # Campos generales
    'NOMBRE_PARROQUIA': 'NOMBRE_PARROQUIA',  # Nombre del Tenant
    'DIRECCION_PARROQUIA': 'DIRECCION_PARROQUIA',
    'DIRECCION_OFICIAL': 'DIRECCION_PARROQUIA',
    'TELEFONO_PARROQUIA': 'TELEFONO_PARROQUIA',
    'TELEFONO_OFICIAL': 'TELEFONO_PARROQUIA',
    'RFC_PARROQUIA': 'RFC_PARROQUIA',
    'RFC_OFICIAL': 'RFC_PARROQUIA',
    'CIUDAD_PARROQUIA': 'CIUDAD_PARROQUIA',
    
    # Datos del solicitante/ciudadano
    'bm_NombreCliente': 'NOMBRE_SOLICITANTE',
    'NOMBRE_CLIENTE': 'NOMBRE_SOLICITANTE',
    'bm_DireccionCliente': 'DIRECCION_SOLICITANTE',
    'DIRECCION_CLIENTE': 'DIRECCION_SOLICITANTE',
    'bm_TelefonoCliente': 'TELEFONO_SOLICITANTE',
    'TELEFONO_CLIENTE': 'TELEFONO_SOLICITANTE',
    'bm_EmailCliente': 'EMAIL_SOLICITANTE',
    'EMAIL_CLIENTE': 'EMAIL_SOLICITANTE',
    'bm_RFC': 'DOCUMENTO_IDENTIDAD',
    'RFC_CLIENTE': 'DOCUMENTO_IDENTIDAD',
    
    # Datos del trámite
    'bm_Fecha': 'FECHA_ACTUAL',
    'FECHA_ACTA': 'FECHA_ACTUAL',
    'FECHA_PAGO': 'FECHA_ACTUAL',
    'FECHA_EMISION': 'FECHA_ACTUAL',
    'FECHA_INHUMACION': 'FECHA_TRAMITE',
    'FECHA_TITULO': 'FECHA_EXPEDICION',
    'FOLIO_ACTA': 'NUMERO_TRAMITE',
    'FOLIO_PAGO': 'NUMERO_RECIBO',
    'FOLIO_TITULO': 'NUMERO_TITULO',
    'FOLIOVENTA': 'NUMERO_TRAMITE',
    
    # Datos específicos de servicios
    'MONTO_TOTAL': 'MONTO_PAGO',
    'MONTO_TOTAL2': 'MONTO_PAGO_LETRA',
    'CIUDAD': 'CIUDAD_PARROQUIA',
    'DIA': 'DIA_ACTUAL',
    'MES': 'MES_ACTUAL',
    'AÑO': 'AÑO_ACTUAL',
    'DOMICILIO_PARROQUIA': 'DIRECCION_PARROQUIA',
    'DIRECCION_PRESTADOR': 'DIRECCION_PARROQUIA',
    
    # Campos adicionales para Acta de Inhumación
    'NOMBRE_FINADO': 'NOMBRE_FALLECIDO',
    'FECHA_NACIMIENTO': 'FECHA_NACIMIENTO_FALLECIDO',
    'FECHA_DEFUNCION': 'FECHA_DEFUNCION',
    'CAUSA_DEFUNCION': 'CAUSA_DEFUNCION',
    'LUGAR_DEFUNCION': 'LUGAR_DEFUNCION',
    'NOMBRE_FAMILIAR': 'NOMBRE_FAMILIAR_RESPONSABLE',
    
    # Campos adicionales para Contrato de Cesión
    'NUMERO_LOTE': 'NUMERO_LOTE',
    'SECCION': 'SECCION_CEMENTERIO',
    'TIPO_SERVICIO': 'TIPO_SERVICIO',
    'DURACION_ANOS': 'DURACION_ANOS',
    'MONTO_ANUAL': 'MONTO_ANUAL',
}


def extract_text_from_element(elem, ns):
    """Extrae todo el texto de un elemento XML incluyendo sus hijos"""
    texts = []
    if elem.text and elem.text.strip():
        texts.append(elem.text.strip())
    
    for child in elem:
        texts.extend(extract_text_from_element(child, ns))
        if child.tail and child.tail.strip():
            texts.append(child.tail.strip())
    
    return texts


def find_and_replace_markers(xml_content, old_marker, new_marker):
    """Busca y reemplaza marcadores en el contenido XML"""
    # El marcador puede estar dividido en múltiples elementos de texto
    # Buscamos patrones como [NOMBRE_PARROQUIA] o partes divididas
    
    # Reemplazo directo en el XML como string primero
    xml_str = xml_content.decode('utf-8')
    
    # Reemplazar marcador completo si existe como texto continuo
    old_pattern = f'[{old_marker}]'
    new_pattern = '{{' + new_marker + '}}'
    xml_str = xml_str.replace(old_pattern, new_pattern)
    
    # También manejar casos donde el marcador pueda estar parcialmente dividido
    # Ejemplo: [ bm_NombreCliente ] con espacios
    old_pattern_space = f'[ {old_marker} ]'
    xml_str = xml_str.replace(old_pattern_space, new_pattern)
    
    return xml_str.encode('utf-8')


def process_template(input_path, output_path=None):
    """Procesa una plantilla .dotx y reemplaza los marcadores"""
    if output_path is None:
        output_path = input_path.replace('.dotx', '_SGIC2.dotx')
    
    # Crear copia temporal
    temp_path = input_path.replace('.dotx', '_temp.dotx')
    shutil.copy2(input_path, temp_path)
    
    try:
        with zipfile.ZipFile(temp_path, 'r') as zip_in:
            # Leer todos los archivos del ZIP
            files = {}
            for name in zip_in.namelist():
                files[name] = zip_in.read(name)
        
        # Procesar document.xml (contenido principal)
        if 'word/document.xml' in files:
            xml_content = files['word/document.xml']
            
            print(f"  Procesando {input_path}...")
            
            # Reemplazar cada marcador
            for old_field, new_field in CAMPOS_MAPEO.items():
                xml_content = find_and_replace_markers(xml_content, old_field, new_field)
            
            files['word/document.xml'] = xml_content
        
        # Crear nuevo archivo con los cambios
        with zipfile.ZipFile(output_path, 'w', zipfile.ZIP_DEFLATED) as zip_out:
            for name, content in files.items():
                zip_out.writestr(name, content)
        
        # Eliminar archivo temporal
        os.remove(temp_path)
        
        print(f"  ✓ Plantilla guardada como: {output_path}")
        return True
        
    except Exception as e:
        print(f"  ✗ Error procesando {input_path}: {e}")
        # Limpiar archivo temporal en caso de error
        if os.path.exists(temp_path):
            os.remove(temp_path)
        return False


def listar_campos_encontrados(dotx_file):
    """Lista todos los campos marcadores encontrados en una plantilla"""
    campos_encontrados = set()
    
    try:
        with zipfile.ZipFile(dotx_file, 'r') as zip_ref:
            xml_content = zip_ref.read('word/document.xml').decode('utf-8')
            
            # Buscar todos los patrones [CAMPO]
            pattern = r'\[([^\]]+)\]'
            matches = re.findall(pattern, xml_content)
            
            for match in matches:
                campo = match.strip()
                if campo:  # Ignorar strings vacíos
                    campos_encontrados.add(campo)
                    
    except Exception as e:
        print(f"Error leyendo {dotx_file}: {e}")
    
    return sorted(campos_encontrados)


def main():
    print("=" * 70)
    print("REFACTORIZACIÓN DE PLANTILLAS PARA SGIC 2.0")
    print("=" * 70)
    
    # Lista de plantillas a procesar
    templates = [
        'Plantilla_ActaInhumacion.dotx',
        'Plantilla_CambioCelular.dotx',
        'Plantilla_ContratoCesioDerechos.dotx',
        'Plantilla_Pagare.dotx',
        'Plantilla_ReciboPago.dotx',
        'Plantilla_TituloPropiedad.dotx'
    ]
    
    print("\n1. ANALIZANDO CAMPOS EXISTENTES EN LAS PLANTILLAS")
    print("-" * 70)
    
    todos_los_campos = set()
    for template in templates:
        if Path(template).exists():
            campos = listar_campos_encontrados(template)
            todos_los_campos.update(campos)
            print(f"\n{template}:")
            for campo in campos:
                estado = "✓ MAPEADO" if campo in CAMPOS_MAPEO else "⚠ SIN MAPEAR"
                nuevo_campo = CAMPOS_MAPEO.get(campo, 'N/A')
                print(f"  - [{campo}] {estado} → {{{{{nuevo_campo}}}}}")
        else:
            print(f"⚠ Archivo no encontrado: {template}")
    
    print("\n\n2. CAMPOS ENCONTRADOS PERO NO MAPEADOS:")
    print("-" * 70)
    campos_no_mapeados = todos_los_campos - set(CAMPOS_MAPEO.keys())
    if campos_no_mapeados:
        for campo in sorted(campos_no_mapeados):
            print(f"  ⚠ [{campo}] - Necesita definición de mapeo")
    else:
        print("  ✓ Todos los campos están mapeados correctamente")
    
    print("\n\n3. PROCESANDO Y GENERANDO NUEVAS PLANTILLAS")
    print("-" * 70)
    
    exitosas = 0
    fallidas = 0
    
    for template in templates:
        if Path(template).exists():
            output_name = template.replace('.dotx', '_SGIC2.dotx')
            if process_template(template, output_name):
                exitosas += 1
            else:
                fallidas += 1
        else:
            print(f"⚠ Saltando archivo no encontrado: {template}")
            fallidas += 1
    
    print("\n" + "=" * 70)
    print(f"RESUMEN: {exitosas} plantillas procesadas exitosamente, {fallidas} fallidas")
    print("=" * 70)
    
    print("\n4. INSTRUCCIONES DE USO CON SGIC 2.0")
    print("-" * 70)
    print("""
Las nuevas plantillas *_SGIC2.dotx ahora usan marcadores Jinja2: {{CAMPO}}

Para usarlas con python-docx-template en tu sistema SGIC 2.0:

```python
from docxtpl import DocxTemplate

# Cargar plantilla
tpl = DocxTemplate('Plantilla_ReciboPago_SGIC2.dotx')

# Contexto con datos del sistema
context = {
    'NOMBRE_PARROQUIA': 'Parroquia San José',
    'DIRECCION_PARROQUIA': 'Av. Principal #123',
    'TELEFONO_PARROQUIA': '555-1234',
    'RFC_PARROQUIA': 'PSJ123456789',
    'NOMBRE_SOLICITANTE': 'Juan Pérez García',
    'DOCUMENTO_IDENTIDAD': 'CURP123456...',
    'DIRECCION_SOLICITANTE': 'Calle 5 #456',
    'TELEFONO_SOLICITANTE': '555-5678',
    'EMAIL_SOLICITANTE': 'juan@email.com',
    'FECHA_ACTUAL': '15 de Enero de 2025',
    'NUMERO_RECIBO': 'REC-2025-001234',
    'MONTO_PAGO': '$1,500.00',
    'MONTO_PAGO_LETRA': 'UN MIL QUINIENTOS PESOS 00/100 M.N.',
    # ... más campos según el documento
}

# Generar documento
tpl.render(context)
tpl.save('documento_generado.docx')
```

NOTA: Los campos específicos de cada documento deben ser proporcionados
según el tipo de trámite (ej. NOMBRE_FALLECIDO para actas de inhumación).
    """)


if __name__ == '__main__':
    main()
