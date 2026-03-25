/**
 * Module Helper - Utilidades para módulos
 * Simplifica las peticiones y rutas de módulos
 */

window.ModuleAPI = {
  /**
   * Obtiene la ruta del controlador de un módulo
   * @param {string} moduleName - Nombre del módulo (ej: 'SoliciTic')
   * @param {string} [controllerName='controller'] - Nombre del archivo del controlador (sin .php)
   * @returns {string} Ruta completa al controlador
   */
  url(moduleName, controllerName = 'controller') {
    const base =
      document.querySelector('base')?.href ||
      `${window.location.origin}/`;

    // Soporta subcarpetas: "GestCompetencia/Evaluaciones" => app/modules/GestCompetencia/Evaluaciones
    const cleanModule = String(moduleName || '')
      .replace(/^\/+|\/+$/g, '')
      .replace(/\\/g, '/');

    return `${base}app/modules/${cleanModule}/controller/${controllerName}.php`;
  },

  /**
   * Realiza una petición POST a un módulo
   * @param {string} moduleName - Nombre del módulo
   * @param {Object} data - Datos a enviar (se convierte a FormData automáticamente)
   * @param {string} [controllerName='controller'] - Nombre del archivo del controlador (sin .php)
   * @returns {Promise<Response>}
   */
  async post(moduleName, data, controllerName = 'controller') {
    const formData = new FormData();

    // Convertir objeto a FormData
    for (const key in data) {
      if (data.hasOwnProperty(key)) {
        formData.append(key, data[key]);
      }
    }

    return fetch(this.url(moduleName, controllerName), {
      method: "POST",
      body: formData,
    });
  },

  /**
   * Realiza una petición POST y devuelve JSON
   * @param {string} moduleName - Nombre del módulo
   * @param {Object} data - Datos a enviar
   * @param {string} [controllerName='controller'] - Nombre del archivo del controlador (sin .php)
   * @returns {Promise<any>}
   */
  async postJSON(moduleName, data, controllerName = 'controller') {
    const response = await this.post(moduleName, data, controllerName);
    return response.json();
  },

  /**
   * Realiza una petición POST y devuelve texto
   * @param {string} moduleName - Nombre del módulo
   * @param {Object} data - Datos a enviar
   * @param {string} [controllerName='controller'] - Nombre del archivo del controlador (sin .php)
   * @returns {Promise<string>}
   */
  async postText(moduleName, data, controllerName = 'controller') {
    const response = await this.post(moduleName, data, controllerName);
    return response.text();
  },
};

/**
 * Alias corto para facilidad
 * @param {string} module - Nombre del módulo
 * @param {string} [controllerName='controller'] - Nombre del archivo del controlador (sin .php)
 * @returns {string} Ruta completa al controlador
 * 
 * @example
 * // Controller por defecto (controller.php)
 * api('CMI_indicador')
 * 
 * @example
 * // Controller específico
 * api('CMI_indicador', 'customController')
 */
window.api = (module, controllerName) => ModuleAPI.url(module, controllerName);

// ¡FUNCIONES UTILES PARA FRONTEND! //

var buttonsLoad = [];

/**
 * Selecciona un elemento del DOM utilizando un selector.
 *
 * @param {string} selector - El selector del elemento a seleccionar.
 * @returns {DOMElement} El elemento seleccionado.
 */
let SELECTOR = (selector) => {
  return document.querySelector(selector);
};

/**
 * Deshabilita un botón y cambia su contenido por un indicador de carga.
 *
 * @param {string} selector - Selector del botón que se va a deshabilitar.
 */
const BUTTONLOADING = (selector, text = null) => {
  let btn = SELECTOR(selector);
  buttonsLoad[selector] = btn.innerHTML;
  btn.disabled = true;
  btn.innerHTML =
    text === null
      ? `<div class="spinner-border text-dark" role="status">
    <span class="visually-hidden">Cargando...</span></div>`
      : text;
};

/**
 * Habilita un botón y restaura su contenido original o personalizado.
 *
 * @param {string} selector - Selector del botón que se va a habilitar.
 * @param {string} [text=null] - Texto opcional para establecer como contenido del botón. Si es null, se restablece el valor original del botón.
 */
const BUTTONLOAD = (selector, text = null) => {
  let btn = SELECTOR(selector);
  if (typeof buttonsLoad[selector] === "undefined" && text === null) {
    text = btn.innerHTML;
  }
  btn.disabled = false;
  btn.innerHTML = text === null ? buttonsLoad[selector] : text;
};

/**
 * Verifica si el texto es una dirección de correo electrónico válida.
 *
 * @param {string} text - Texto que se verificará si es un email.
 * @returns {boolean} - Devuelve true si el texto es un email válido, de lo contrario, devuelve false.
 */
function isEmail(text) {
  var mailformat =
    /^[a-zA-Z0-9ñÑ]([a-zA-Z0-9._+-ñÑ]*[a-zA-Z0-9ñÑ])?@[a-zA-Z0-9ñÑ]([a-zA-Z0-9.\-ñÑ]*[a-zA-Z0-9ñÑ])?\.[a-zA-Z]{2,}$/;
  return text.match(mailformat);
}

/**
 * Descarga un archivo utilizando un objeto Blob o una URL de datos.
 *
 * @param {Blob|String} blob - Objeto Blob o URL de datos para descargar.
 * @param {String} name - Nombre del archivo descargado.
 * @param {Function} [fun=() => {}] - Función que se ejecutará después de finalizar la descarga.
 */
function downloadElement(blob, name, fun = () => {}) {
  if (typeof blob != "string") {
    blob = window.URL.createObjectURL(blob);
  }
  let url = blob;
  let a = document.createElement("a");
  a.href = url;
  a.download = name;
  document.body.appendChild(a);
  a.click();
  a.remove();
  fun();
}

/**
 * Muestra una notificación utilizando múltiples librerías (Toastr, SweetAlert2 o alert nativo).
 *
 * @param {string} mensaje - El mensaje de la notificación.
 * @param {string} tipo - El tipo de notificación (success, error, warning, info).
 * @param {boolean} isToast - Indica si la notificación debe ser un toast (notificación pequeña en esquina).
 * @param {string} libreria - Librería preferida: 'toastr', 'swal', 'auto' (detecta automáticamente). Por defecto: 'auto'.
 */
function mostrarNotificacion(
  mensaje,
  tipo = "info",
  isToast = true,
  libreria = "auto"
) {
  const iconos = {
    success: "success",
    error: "error",
    warning: "warning",
    info: "info",
  };

  const tipoIcono = iconos[tipo] || "info";

  // Si se especifica una librería, intentar usarla primero
  if (libreria === "toastr" && typeof toastr !== "undefined") {
    toastr[tipo](mensaje);
    return;
  }

  if (libreria === "swal" && typeof Swal !== "undefined") {
    if (isToast) {
      Swal.fire({
        icon: tipoIcono,
        title: mensaje,
        toast: true,
        position: "bottom-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    } else {
      Swal.fire({
        icon: tipoIcono,
        title: mensaje,
        toast: false,
        position: "center",
        showConfirmButton: true,
      });
    }
    return;
  }

  // Detección automática (auto)
  if (typeof toastr !== "undefined") {
    toastr[tipo](mensaje);
  } else if (typeof Swal !== "undefined") {
    if (isToast) {
      Swal.fire({
        icon: tipoIcono,
        title: mensaje,
        toast: true,
        position: "bottom-end",
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
      });
    } else {
      Swal.fire({
        icon: tipoIcono,
        title: mensaje,
        toast: false,
        position: "center",
        showConfirmButton: true,
      });
    }
  } else {
    // Fallback con alert nativo
    alert(mensaje);
  }
}

/// FUNCIONES DE Valores ///
function formatoConComas(input) {
  let valor = input.value.replace(/[^\d]/g, ""); // Eliminamos todos los caracteres no numéricos

  // Si hay un cero al principio, pero el número es mayor a cero, eliminamos el cero
  if (valor.length > 1 && valor[0] === "0") {
    valor = valor.slice(1);
  }

  // Insertamos comas cada tres dígitos desde el final de la cadena
  let valorFormateado = "";
  let startIndex = valor.length % 3 || 3;
  valorFormateado = valor.slice(0, startIndex);
  while (startIndex < valor.length) {
    valorFormateado += "," + valor.slice(startIndex, startIndex + 3);
    startIndex += 3;
  }

  input.value = valorFormateado;
}

/**
 * Genera y descarga un archivo Excel desde una tabla HTML.
 * Utiliza la librería TableToExcel si está disponible, de lo contrario usa un método alternativo.
 *
 * @param {string} tableId - ID de la tabla HTML (sin el #). Ejemplo: 'miTabla' o '#miTabla'
 * @param {string} [nombreArchivo='Datos'] - Nombre del archivo Excel a descargar (sin extensión)
 * @param {string} [nombreHoja='Hoja 1'] - Nombre de la hoja dentro del Excel
 * @returns {void}
 *
 * @example
 * // Generar Excel simple
 * generarExcel('miTabla');
 *
 * @example
 * // Generar Excel con nombre personalizado
 * generarExcel('miTabla', 'Reporte_Ventas');
 *
 * @example
 * // Generar Excel con nombre de archivo y hoja personalizados
 * generarExcel('#tabla-datos', 'Informe_2026', 'Datos Enero');
 */
function generarExcel(tableId, nombreArchivo = 'Datos', nombreHoja = 'Hoja 1') {
  // Remover el # si viene en el ID
  const id = tableId.replace('#', '');
  
  // Buscar la tabla en el DOM
  let tabla = document.getElementById(id);
  
  if (!tabla) {
    console.error(`No se encontró la tabla con ID: ${id}`);
    mostrarNotificacion(`No se encontró la tabla con ID: ${id}`, 'error');
    return;
  }

  // Crear una copia de la tabla para exportar
  let tablaExportar = tabla.cloneNode(true);
  
  // Detectar si es una tabla DataTable
  if (typeof jQuery !== 'undefined' && jQuery.fn.DataTable && jQuery.fn.DataTable.isDataTable('#' + id)) {
    console.log('Detectado: Tabla con DataTable - exportando TODOS los datos');
    
    try {
      const dt = jQuery('#' + id).DataTable();

      // Detectar serverSide (si está activado no podemos forzar que el servidor devuelva todo)
      const settings = dt.settings && dt.settings()[0] ? dt.settings()[0] : null;
      const serverSide = settings && settings.oFeatures && settings.oFeatures.bServerSide;

      // Limpiar tbody del clon
      const tbody = tablaExportar.querySelector('tbody') || tablaExportar.appendChild(document.createElement('tbody'));
      tbody.innerHTML = '';

      if (!serverSide) {
        // Guardar estado actual de paginación para restaurarlo luego
        const currentLen = dt.page.len();
        const currentPage = dt.page();

        // Forzar mostrar todas las filas cambiando el page length al total de filas actuales (filtrado aplicado)
        const totalRows = dt.rows({ search: 'applied' }).count();
        dt.page.len(totalRows).draw(false);

        // Tomar las filas actuales del DOM (ahora deberían contener todas las filas)
        const srcRows = jQuery('#' + id + ' tbody tr').toArray();
        srcRows.forEach(row => tbody.appendChild(row.cloneNode(true)));

        // Restaurar paginación anterior
        dt.page.len(currentLen).draw(false);
        dt.page(currentPage).draw(false);

        console.log(`Total de filas exportadas: ${srcRows.length}`);
      } else {
        // En modo serverSide no hay todo el dataset en el cliente. Intentamos obtener los nodos disponibles.
        const allData = dt.rows({ search: 'applied' }).nodes();
        allData.forEach(row => tbody.appendChild(row.cloneNode(true)));
        console.log(`Exportando filas disponibles en cliente (serverSide): ${allData.length}`);
      }

    } catch (error) {
      console.warn('Error al extraer datos de DataTable:', error);
      // Si hay error, usar la tabla tal como está
      tablaExportar = tabla.cloneNode(true);
    }
  }
  
  // Remover elementos innecesarios (paginación, filtros, etc.)
  const elementosARemover = tablaExportar.querySelectorAll('.dataTables_paginate, .dataTables_filter, .dataTables_length, .dataTables_info, tr.filters');
  elementosARemover.forEach(elem => elem.remove());

  // Procesar todas las celdas para extraer números formateados y convertirlos a valores puros
  // Automáticamente detecta y limpia números con formato español (1.500.000 o 1.500.000,50)
  const todasLasCeldas = tablaExportar.querySelectorAll('td, th');
  todasLasCeldas.forEach(celda => {
    // Si la celda tiene data-value, usarlo como prioridad
    const valorDataAttr = celda.getAttribute('data-value');
    if (valorDataAttr && valorDataAttr !== '') {
      celda.textContent = valorDataAttr;
      return;
    }
    
    // Obtener el texto visible de la celda (sin HTML)
    let texto = celda.textContent.trim();
    
    // Detectar si parece un número formateado (con puntos y/o comas)
    // Patrones: 1.500.000 o 1.500.000,50 o -1.500.000,50
    if (/^-?\d{1,3}(\.\d{3})*([,\.]\d{1,2})?$/.test(texto)) {
      // Reemplazar separador de miles (.) y decimal (,) con estándares internacionales
      let numeroLimpio = texto
        .replace(/\./g, '')      // Remover puntos (separadores de miles)
        .replace(',', '.');      // Reemplazar coma por punto (decimal)
      
      celda.textContent = numeroLimpio;
    }
  });

  // Verificar si TableToExcel está disponible
  if (typeof TableToExcel !== 'undefined') {
    try {
      TableToExcel.convert(tablaExportar, {
        name: `${nombreArchivo}.xlsx`,
        sheet: {
          name: nombreHoja
        }
      });
      mostrarNotificacion('Excel generado correctamente con todos los datos', 'success');
    } catch (error) {
      console.error('Error al generar Excel con TableToExcel:', error);
      mostrarNotificacion('Error al generar el archivo Excel', 'error');
    }
  } else if (typeof XLSX !== 'undefined') {
    // Usar SheetJS como segunda opción para generar .xlsx
    console.log('Usando SheetJS para generar Excel');
    generarExcelConSheetJS(tablaExportar, nombreArchivo, nombreHoja);
  } else {
    // Método alternativo usando exportación CSV si ninguna librería está disponible
    console.warn('No hay librerías de Excel disponibles, usando método alternativo CSV');
    generarCSV(tablaExportar, nombreArchivo);
  }
}

/**
 * Genera Excel usando la librería SheetJS (XLSX)
 * @private
 */
function generarExcelConSheetJS(tabla, nombreArchivo, nombreHoja) {
  try {
    // Obtener datos de la tabla
    const ws = XLSX.utils.table_to_sheet(tabla);
    
    // Procesar celdas para asegurar que los números sean tratados como números, no texto
    // Recorrer todas las celdas de la hoja
    for (const celda in ws) {
      // Saltar metadatos de la hoja (!ref, !margins, etc.)
      if (celda.startsWith('!')) continue;
      
      const cell = ws[celda];
      if (cell && cell.v !== undefined) {
        // Si el valor es una cadena que contiene solo números, convertirlo a número
        if (typeof cell.v === 'string') {
          const numValue = parseFloat(cell.v.replace(/\./g, '').replace(',', '.'));
          if (!isNaN(numValue) && cell.v.match(/^-?\d+([.,]\d+)?$/)) {
            // Es un número formateado, convertir a número puro
            cell.v = numValue;
            cell.t = 'n'; // Marcar como tipo número
          }
        }
      }
    }
    
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, nombreHoja);
    XLSX.writeFile(wb, `${nombreArchivo}.xlsx`);
    mostrarNotificacion('Excel generado correctamente', 'success');
  } catch (error) {
    console.error('Error al generar Excel con SheetJS:', error);
    mostrarNotificacion('Error al generar el archivo Excel', 'error');
  }
}

/**
 * Método alternativo para exportar tabla como CSV
 * Se usa cuando TableToExcel no está disponible
 * @private
 */
function generarCSV(tabla, nombreArchivo) {
  try {
    let csv = [];
    const rows = tabla.querySelectorAll('tr');
    
    for (let i = 0; i < rows.length; i++) {
      const row = [];
      const cols = rows[i].querySelectorAll('td, th');
      
      for (let j = 0; j < cols.length; j++) {
        // Limpiar el texto y escapar comillas
        let data = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ');
        data = data.replace(/"/g, '""');
        row.push('"' + data + '"');
      }
      
      csv.push(row.join(','));
    }
    
    // Crear el archivo CSV
    const csvString = csv.join('\n');
    const blob = new Blob(['\ufeff' + csvString], { type: 'text/csv;charset=utf-8;' });
    
    // Descargar
    downloadElement(blob, `${nombreArchivo}.csv`);
    mostrarNotificacion('CSV generado correctamente', 'success');
  } catch (error) {
    console.error('Error al generar CSV:', error);
    mostrarNotificacion('Error al generar el archivo', 'error');
  }
}
